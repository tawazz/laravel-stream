<?php
namespace App\Http\Responses;

use Illuminate\Http\Request;
use Storage;

class S3FileStream
{
    /**
     * @var \League\Flysystem\AwsS3v3\AwsS3Adapter
     */
    private $adapter;

    /**
     * @var \Aws\S3\S3Client
     */
    private $client;

    /**
     * @var file end byte
     */
    private $end;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool storing if request is a range (or a full file)
     */
    private $isRange = false;

    /**
     * @var length of bytes requested
     */
    private $length;

    /**
     * @var
     */
    private $return_headers = [];

    /**
     * @var file size
     */
    private $size;

    /**
     * @var start byte
     */
    private $start;

    /**
     * S3FileStream constructor.
     * @param string $filePath
     * @param string $adapter
     */
    public function __construct(string $filePath, string $adapter = 's3')
    {
        $this->filePath   = $filePath;
        $this->filesystem = Storage::disk($adapter)->getDriver();
        $this->adapter    = Storage::disk($adapter)->getAdapter();
        $this->client     = $this->adapter->getClient();
    }

    /**
     * Output file to client
     */
    public function output()
    {
        return $this->setHeaders()->stream();
    }

    /**
     * Output headers to client
     * @return $this
     */
    protected function setHeaders()
    {
        $object = $this->client->headObject([
            'Bucket' => $this->adapter->getBucket(),
            'Key'    => $this->filePath,
        ]);

        $this->start = 0;
        $this->size  = $object['ContentLength'];
        $this->end   = $this->size - 1;
        //Set headers
        $this->return_headers                        = [];
        $this->return_headers['Last-Modified']       = $object['LastModified'];
        $this->return_headers['Accept-Ranges']       = 'bytes';
        $this->return_headers['Content-Type']        = $object['ContentType'];
        $this->return_headers['Content-Disposition'] = 'inline; filename=' . basename($this->filePath);

        if (!is_null(request()->server('HTTP_RANGE'))) {
            $c_start = $this->start;
            $c_end   = $this->end;

            [$_, $range] = explode('=', request()->server('HTTP_RANGE'), 2);
            if (strpos($range, ',') !== false) {
                headers('Content-Range: bytes ' . $this->start . '-' . $this->end . '/' . $this->size);
                return response('416 Requested Range Not Satisfiable', 416);
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            } else {
                $range   = explode('-', $range);
                $c_start = $range[0];

                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                headers('Content-Range: bytes ' . $this->start . '-' . $this->end . '/' . $this->size);
                return response('416 Requested Range Not Satisfiable', 416);
            }
            $this->start                            = $c_start;
            $this->end                              = $c_end;
            $this->length                           = $this->end - $this->start + 1;
            $this->return_headers['Content-Length'] = $this->length;
            $this->return_headers['Content-Range']  = 'bytes ' . $this->start . '-' . $this->end . '/' . $this->size;
            $this->isRange                          = true;
        } else {
            $this->length                           = $this->size;
            $this->return_headers['Content-Length'] = $this->length;
            unset($this->return_headers['Content-Range']);
            $this->isRange = false;
        }

        return $this;
    }

    /**
     * Stream file to client
     * @throws \Exception
     */
    protected function stream()
    {
        $this->client->registerStreamWrapper();
        // Create a stream context to allow seeking
        $context = stream_context_create([
            's3' => [
                'seekable' => true,
            ],
        ]);
        // Open a stream in read-only mode
        if (!($stream = fopen("s3://{$this->adapter->getBucket()}/{$this->filePath}", 'rb', false, $context))) {
            throw new \Exception('Could not open stream for reading export [' . $this->filePath . ']');
        }
        if (isset($this->start)) {
            fseek($stream, $this->start, SEEK_SET);
        }

        $remaining_bytes = $this->length ?? $this->size;
        $chunk_size      = 1024;

        $video = response()->stream(
            function () use ($stream, $remaining_bytes, $chunk_size) {
                while (!feof($stream) && $remaining_bytes > 0) {
                    echo fread($stream, $chunk_size);
                    $remaining_bytes -= $chunk_size;
                    flush();
                }
                fclose($stream);
            },
            ($this->isRange ? 206 : 200),
            $this->return_headers
        );
        return $video;
    }
}
