@extends('layouts.page')

@section('page_title')
{{ trans('navigation.questions') }}
@stop

@section('body')
        <h2>{{ trans('messages.editing')}} {{ trans('ques.of_question')}} <span class='imp'>"{{ $question->name}}"</span></h2>
        <p><a href="/ques/question/{{$question->id}}{{$args_by_get}}">{{ trans('messages.back_to_show') }}</a></p>
        
        {!! Form::model($question, array('method'=>'PUT', 'route' => array('question.update', $question->id))) !!}
        @include('ques.question._form_create_edit', ['submit_title' => trans('messages.save'),
                                      'action' => 'edit'])
        {!! Form::close() !!}
@stop