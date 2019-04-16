@extends('admin.layout')

@section('admin-title') Species @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Species' => 'admin/data/species', ($species->id ? 'Edit' : 'Create').' Species' => $species->id ? 'admin/data/species/edit/'.$species->id : 'admin/data/species/create']) !!}

<h1>{{ $species->id ? 'Edit' : 'Create' }} Species
    @if($species->id)
        <a href="#" class="btn btn-danger float-right delete-species-button">Delete Species</a>
    @endif
</h1>

{!! Form::open(['url' => $species->id ? 'admin/data/species/edit/'.$species->id : 'admin/data/species/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $species->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 200px x 200px</div>
    @if($species->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $species->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="text-right">
    {!! Form::submit($species->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($species->id)
    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._entry', ['imageUrl' => $species->speciesImageUrl, 'name' => $species->displayName, 'description' => $species->parsed_description])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {    
    $('.delete-species-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/species/delete') }}/{{ $species->id }}", 'Delete Species');
    });
});
    
</script>
@endsection