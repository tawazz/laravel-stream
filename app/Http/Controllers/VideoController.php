<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Jobs\ConvertVideoForStreaming;
use App\Video;
use Illuminate\Http\Request;
use Storage;
use App\Http\Resources\VideoResource;

class VideoController extends Controller
{

    /**
     * Return video blade view and pass videos to it.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $videos = Video::orderBy('created_at', 'DESC')->get();
        return view('videos')->with('videos', $videos);
    }

    /**
     * Return uploader form view for uploading videos
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function uploader()
    {
        return view('uploader');
    }

    /**
     * Handles form submission after uploader form submits
     * @param StoreVideoRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreVideoRequest $request)
    {
        $path = str_random(16) . '.' . $request->video->getClientOriginalExtension();
        $request->video->storeAs('public', $path);

        $video = Video::create([
            'disk'          => 'public',
            'original_name' => $request->video->getClientOriginalName(),
            'path'          => $path,
            'title'         => $request->title,
        ]);

        dispatch(new ConvertVideoForStreaming($video))->onQueue('high');

        return redirect('/')
            ->with(
                'message',
                'Your video will be available shortly after we process it'
            );
    }

    public function stream(Request $request)
    {
        $video = Video::wherePath($request->video)->firstOrFail();
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        $file =  $storagePath.'public/'.$video->stream_path;

        $filestream = new \App\Http\Responses\S3FileStream($video->stream_path);
        return $filestream->output();
        $mime = 'video/mp4';
        $size = filesize($file);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        header(sprintf('Content-type: %s', $mime));
        header('Accept-Ranges: bytes');

        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));

                exit;
            }

            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;

            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));

                exit;
            }

            header('HTTP/1.1 206 Partial Content');

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
        }

        header("Content-Range: bytes $start-$end/$size");
        header(sprintf('Content-Length: %d', $length));

        $fh = fopen($file, 'rb');
        $buffer = 1024 * 8;

        fseek($fh, $start);

        while (true) {
            if (ftell($fh) >= $end) {
                break;
            }

            set_time_limit(0);

            echo fread($fh, $buffer);

            flush();
        }
    }

    public function delete(Video $video)
    {
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().'public/';
        try {
            unlink($storagePath.$video->stream_path);
            unlink($storagePath.$video->poster_path);
            unlink($storagePath.$video->thumbnail_path);
        } catch (\Exception $e) {
            dump($e);
        }
        $video->delete();
        return redirect('/')->with('message', "deleted");
    }

    public function apiIndex()
    {
        $videos = Video::whereProcessed(true)->latest()->get();
        return VideoResource::collection($videos);
    }
}
