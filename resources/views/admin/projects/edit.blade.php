@extends('layouts.admin')
@section('title', $project->name)

@section('content')
    @include('admin.projects._form')
@endsection
