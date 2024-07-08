@extends('layouts.app')
@section('content')
<!-- HTML output -->
@if(!empty($title))
<div class="title">{{$title}}</div>
@endif

@if(!empty($message))
<div class="subtitle">{!! $message !!}</div>
@endif
<div>
        <a href="{{$url}}">
            <button>Go back</button>
        </a>
    </div>

@stop