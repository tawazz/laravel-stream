<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Video;

class UploadToCloud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public function __construct($videoId)
    {
      $this->video = Video::find($videoId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
      $stream_path = Storage::disk('s3')->putFile('videos', new File($storagePath.'public/'.$this->video->path), 'public');
      // update the database so we know the upload is done!
      $this->video->update([
          'stream_path' => $stream_path
      ]);
      unlink($storagePath.'public/'.$this->video->path);
      unlink($storagePath.'public/streams/'.$this->video->path);
    }
}
