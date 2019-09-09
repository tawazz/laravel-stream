@extends('layouts.app')

@section('content')
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 mr-auto ml-auto mt-5">
        <h3 class="text-center">
            Videos
        </h3>

        @foreach($videos as $video)
            <div class="row mt-5">
                <div class="video" >
                    <div class="title">
                        <h4>
                            {{$video->title}}
                        </h4>
                    </div>
                    @if($video->processed)
                        <video   class="w-100 player" crossorigin controls playsinline  poster="/storage/posters/{{ substr($video->path, 0, -3).'jpg'}}">
                            <source src="/stream/{{$video->id}}" type="video/mp4" size="576"/>
                        </video>
                    @else
                        <div class="alert alert-info w-100">
                             Video is currently being processed and will be available shortly
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="wrapper container-fluid">
      <section id="section1" class="row">
          @if (sizeof($videos) > 4)
              <a href="#section3">‹</a>
          @endif

          @foreach($videos as $video)
          <div class="item">
            <img src="/storage/posters/thumbnail/{{ substr($video->path, 0, -3).'jpg'}}" height="300px"/>
          </div>
          @endforeach
          @if (sizeof($videos) > 4)
              <a href="#section3">›</a>
          @endif
      </section>
    </div>
@endSection
