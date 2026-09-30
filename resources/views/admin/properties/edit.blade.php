@extends('layouts.admin')
@section('title', $property->title)

@section('content')
    @include('admin.properties._form')
@endsection
