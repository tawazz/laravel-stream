@extends('layouts.app')

@section('content')
    <div class="row">
        @foreach($videos as $video)
        <div class="col-md-6">
            @if($video->processed)
                <!-- Card -->
                <div class="card card-cascade wider reverse">
                    <!-- Card image -->
                    <div class="view overlay">
                        <video class="w-100 player card-img-top" crossorigin controls playsinline poster="/storage/{{ $video->poster_path }}">
                            <source src="/stream/{{$video->id}}" type="video/mp4" size="576" />
                        </video>
                    </div>

                    <!-- Card content -->
                    <div class="card-body">
                        <h4 class="card-title text-capitalize">{{$video->title}}</h4>
                    </div>
                    <div class="card-footer">
                      <form action="/videos/delete/{{$video->id}}" method="post">
                          @csrf
                          <button class="btn btn-danger btn-rounded float-right" type="submit">Delete</button>
                      </form>
                    </div>

                </div>
                <!-- Card -->
                @else
                <div class="alert alert-info w-100">
                    Video {{ $video->title }} is currently being processed and will be available shortly
                    <div class="progress">
                        <div id="video_{{$video->id}}" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>
                @endif
        </div>
        @endforeach
    </div>
@endSection
