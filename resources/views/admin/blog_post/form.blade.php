@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($blogPost) ? __('Edit Blog Post') : __('Add Blog Post')" 
        :back_route="route('admin.blog_post.list')"
    />

    <form class="ajax-form" action="{{ route('admin.blog_post.save', $blogPost->id ?? null) }}" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-9">
                @csrf

                <x-card>
                    <div class="row gy-4">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" class="form-control" name="title"
                                    value="{{ old('title', $blogPost->title ?? '') }}" placeholder="@lang('Enter post title')" />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Post Image')</label>
                                <input type="file" class="form-control" name="image" accept="image/jpeg,image/jpg,image/png,image/webp" />
                                <small class="text-muted">
                                    {{ imageSize('blog') }} &mdash; @lang('Large images are automatically optimized/compressed.')
                                </small>
                                @if (!empty($blogPost->image))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $blogPost->image) }}" alt="Post Image"
                                            height="60">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">@lang('Post Body')</label>


                <textarea name="body" id="post-editor">{{ old('body', $blogPost->body ?? '') }}</textarea>


                            </div>
                        </div>
                    </div>
                </x-card>

                <div class="alert alert-info mt-4 mb-0">
                    <i class="fa fa-info-circle"></i>
                    @lang('SEO title, meta description, Open Graph tags and the social share image are generated automatically from the title, content and featured image above — no separate SEO setup is needed.')
                </div>

            </div>

            <div class="col-lg-3">
                <aside class="software-sidebar">
                    <x-card>
                        <select class="form-control select2" name="status">
                            <option value="1" @selected(isset($blogPost) && $blogPost?->status == 1 ?? false)>@lang('Published')</option>
                            <option value="0" @selected(isset($blogPost) && $blogPost?->status == 0 ?? false)>@lang('Unpublished')</option>
                        </select>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <x-button type="submit">
                                <x-icons.save />
                                @lang('Save')
                            </x-button>
                            <x-button id="save-and-back" type="submit" class="btn-danger">
                                <x-icons.save />
                                @lang('Save & Exit')
                            </x-button>
                        </div>
                    </x-card>
                </aside>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/shared/css/tagify.css') }}">
        {{-- post body css cdn --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    {{-- post body css cdn end --}}
@endpush

@push('scripts')
    
    
{{-- post body styles --}}
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

{{-- post body styles end --}}
    
    
    <script src="{{ asset('assets/shared/js/tagify.js') }}"></script>

    <script>
        $(document).ready(function() {
            SystemHelper.ajaxSubmit(
                $('.ajax-form')
            );

        });
        
        
        
        
        
    // summernot 

  $(document).ready(function() {
      $('#post-editor').summernote({
          placeholder: 'Write your news article here about SDF...',
          height: 500,
          tabsize: 2,
          toolbar: [
              ['style', ['style']],
              ['font', ['bold', 'underline', 'italic', 'clear']],
              ['color', ['color']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['table', ['table']],
              ['insert', ['link', 'picture', 'video', 'hr']],
              ['view', ['fullscreen', 'codeview', 'help']]
          ],
          styleTags: ['p', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
      });
  });
        
        
        
        
        
        
    </script>
@endpush
