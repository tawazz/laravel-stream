<?php

namespace App\Jobs;

use Storage;
use FFMpeg;
use App\Video;
use Carbon\Carbon;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;
use FFMpeg\Media\Video as FFMpegVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConvertVideoForStreaming implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    /**
     * Create a new job instance.
     *
     * @param Video $video
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // create a video format...
        $lowBitrateFormat = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
        $lowBitrateFormat->on('progress', function($video, $format, $percentage) {
            echo "$percentage % \r";
        });

        $converted_name = $this->getCleanFileName($this->video->original_name);

       

        // open the uploaded video from the right disk...
        $video = FFMpeg::fromDisk($this->video->disk)->open($this->video->path);

        //save poster
        $frame = $video->getFrameFromTimecode(FFMpeg\Coordinate\TimeCode::fromSeconds(5));
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        $posterPath=  $storagePath.'public/posters/'.str_replace('.mp4', '.jpg', $converted_name);
        $frame->save($posterPath);

        // convert video
        FFMpeg::fromDisk($this->video->disk)->open($this->video->path)->addFilter(function ($filters) {
            $filters->resize(new Dimension(960, 540));
        })

        // call the 'export' method...
        ->export()

        // tell the MediaExporter to which disk and in which format we want to export...
        ->toDisk('public')
        ->inFormat($lowBitrateFormat)

        // call the 'save' method with a filename...
        ->save($converted_name);

        // update the database so we know the convertion is done!
        $this->video->update([
            'converted_for_streaming_at' => Carbon::now(),
            'processed' => true,
            'stream_path' => $converted_name
        ]);
    }

    private function getCleanFileName($filename){
        return preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename) . '.mp4';
    }
}
