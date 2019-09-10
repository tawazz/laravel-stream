@extends('layouts.app')

@section('content')
<div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 mr-auto ml-auto mt-5">
    <h3 class="text-center">
        Upload Video
    </h3>
    <form method="post" action="{{ route('upload') }}" enctype="multipart/form-data">
        <div class="form-group">
            <label for="video-title">Title</label>
            <input type="text" class="form-control" name="title" placeholder="Enter video title">
            @if($errors->has('title'))
                <span class="text-danger">
                    {{$errors->first('title')}}
                </span>
                @endif
        </div>
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Upload</span>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="video">
                    <label class="custom-file-label" >Choose file</label>
                </div>
            </div>
            @if($errors->has('video'))
            <span class="text-danger">
                {{$errors->first('video')}}
            </span>
            @endif
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary btn-rounded">
        </div>

        {{csrf_field()}}
    </form>
</div>
@endSection
