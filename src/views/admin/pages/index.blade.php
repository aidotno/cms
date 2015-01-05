@extends('devise::admin.layouts.master')

@section('title')
    <div id="dvs-admin-title">
        <h1>List of Pages</h1>
    </div>

    <div id="dvs-admin-actions">
        {{ Form::select('language_id', $languages, (!Input::has('language_id')) ? 45 : Input::get('language_id'), array('id' => 'lang-select', 'class' => 'dvs-select')) }}</label>
        {{ link_to(URL::route('dvs-pages-create'), 'Create New Page', array('class'=>'dvs-button')) }}
    </div>
@stop

@section('main')
    <table class="dvs-admin-table">
		<thead>
			<tr>
				<th class="dvs-tal">
                    <div class="dvs-inline-block dvs-tal">
                        {{ Sort::filter('slug', "#pages, #pagination-links", ['placeholder' => 'Filter by Slug', 'class' => 'filter-by-slug']) }}
                    </div>
                    <div class="dvs-inline-block dvs-button-group dvs-sort-group dvs-pr">
                        {{ Sort::link('slug','Path', array('class' => 'dvs-button dvs-button-small dvs-button-outset')) }}
                        {{ Sort::link('title','Page Name', array('class' => 'dvs-button dvs-button-small dvs-button-outset')) }}
                    </div>
                </th>
				<th>Languages</th>
				<th>{{ Sort::link('is_admin','Admin') }}</th>
				<th>{{ Sort::clearSortLink('Clear Sort', array('class'=>'dvs-button dvs-button-small dvs-button-outset')) }}</th>
			</tr>
		</thead>

		<tbody id="pages">
			@foreach($pages as $page)
				<tr>
					<td class="dvs-stacked-col dvs-tal">
                        <div>{{ $page->title }}</div>
                        <div class="dvs-inset-text">{{ HTML::filterLinkParts($page->slug) }}</div>
                        <a class="dvs-expand-details" href="javascript:void(0)">+ Expand Page Versions</a>
                    </td>
                    <td class="dvs-tac">{{ HTML::showLanguagesForPages($page->availableLanguages, true) }}</td>
					<td class="dvs-tac">{{ ($page->is_admin) ? 'Yes' : 'No' }}</td>
					<td class="dvs-tac dvs-button-group">
						{{ link_to($page->slug, 'View', array('class'=>'dvs-button dvs-button-small dvs-button-secondary')) }}
						{{ link_to(URL::route('dvs-pages-edit', array($page->id)), 'Edit', array('class'=>'dvs-button dvs-button-small')) }}
						{{ link_to(URL::route('dvs-pages-copy', array($page->id)), 'Copy', array('class'=>'dvs-button dvs-button-small')) }}

                        @if(!$page->dvs_admin)
						  {{Form::delete(URL::route('dvs-pages-destroy', array($page->id)), 'Delete', null, array('class'=>'dvs-button dvs-button-small dvs-button-danger'))}}
						@endif
	                </td>
				</tr>

                <tr class="dvs-page-details dvs-collapsed">
                    <td colspan="4">

                        @include('devise::admin.pages.page-versions._cards')

                    </td>
                </tr>
			@endforeach
		</tbody>

        <tfoot>
            <tr id="pagination-links">
                <td colspan="4">{{ $pages->appends(Input::except(['page']))->links(); }}</td>
            </tr>
        </tfoot>
	</table>

	<script>devise.require(['app/admin/admin', 'app/bindings/data-dvs-replacement', 'app/bindings/data-change-target'])</script>
@stop