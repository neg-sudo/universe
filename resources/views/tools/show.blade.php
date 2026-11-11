@extends('layouts.app')

@section('title', $tool->name.' — UniVerse')
@section('description', $tool->description)

@section('content')
    <section class="page-head page-head--compact">
        <div class="container">
            <x-breadcrumbs :items="[
                ['label' => 'Tools', 'url' => route('tools.index')],
                ['label' => $tool->category->name, 'url' => route('tools.index', ['category' => $tool->category->slug])],
                ['label' => $tool->name],
            ]" />

            <div class="tool-heading">
                <span class="tool-heading__icon" aria-hidden="true">
                    <x-icon :name="$tool->icon ?? 'sparkles'" :size="28" />
                </span>
                <div>
                    <div class="meta-row">
                        <a class="pill" href="{{ route('tools.index', ['category' => $tool->category->slug]) }}">{{ $tool->category->name }}</a>
                        <span class="meta-item"><x-icon name="zap" :size="14" /> Runs in your browser</span>
                        <span class="meta-item"><x-icon name="file-check" :size="14" /> Tested v{{ $tool->version }}</span>
                    </div>
                    <h1>{{ $tool->name }}</h1>
                    <p class="page-head__lede">{{ $tool->description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container">
            <div class="tool-layout">
                <div class="tool-panel">
                    <div class="panel-head">
                        <h2>Enter your values</h2>
                        <p>Results update as you type. Nothing is sent to a server.</p>
                    </div>

                    <form class="tool-form" data-tool="{{ $tool->slug }}" data-track-url="{{ route('tools.track', $tool->slug) }}">
                        @foreach ($tool->inputs as $input)
                            @php
                                $name = $input['name'];
                                $type = $input['type'];
                                $id = 'f-'.$tool->slug.'-'.$name;
                                $default = $input['default'] ?? null;
                                $required = ! empty($input['required']);
                                $showIf = $input['showIf'] ?? null;
                            @endphp

                            <div class="field"
                                 data-input
                                 @if ($showIf) data-show-if="{{ json_encode($showIf) }}" @endif
                                 @if ($showIf) hidden @endif>
                                @if ($type === 'checkbox')
                                    <label class="checkbox" for="{{ $id }}">
                                        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $default)>
                                        <span>{{ $input['label'] }}</span>
                                    </label>
                                @else
                                    <label class="field__label" for="{{ $id }}">
                                        {{ $input['label'] }}
                                        @if ($required)<span class="field__required" aria-hidden="true">*</span>@endif
                                    </label>

                                    @if ($type === 'textarea')
                                        <textarea id="{{ $id }}" name="{{ $name }}" rows="6"
                                                  placeholder="{{ $input['placeholder'] ?? '' }}"
                                                  @if ($required) required @endif>{{ $default }}</textarea>
                                    @elseif ($type === 'select')
                                        <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif>
                                            @foreach ($input['options'] as $option)
                                                <option value="{{ $option['value'] }}" @selected((string) $default === (string) $option['value'])>
                                                    {{ $option['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
                                               value="{{ $default }}"
                                               placeholder="{{ $input['placeholder'] ?? '' }}"
                                               @if (isset($input['min'])) min="{{ $input['min'] }}" @endif
                                               @if (isset($input['max'])) max="{{ $input['max'] }}" @endif
                                               step="{{ $input['step'] ?? ($type === 'number' ? 'any' : '') }}"
                                               @if ($required) required @endif>
                                    @endif
                                @endif

                                @if (! empty($input['hint']))
                                    <p class="field__hint">{{ $input['hint'] }}</p>
                                @endif
                            </div>
                        @endforeach

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">
                                Calculate
                                <x-icon name="arrow-right" :size="17" />
                            </button>
                            <button class="btn btn--ghost" type="reset">Reset</button>
                        </div>
                    </form>

                    <p class="privacy-note">
                        <x-icon name="shield" :size="15" />
                        Calculations run locally in your browser. Your inputs are never uploaded.
                    </p>

                    <noscript>
                        <div class="notice notice--warning">
                            This tool needs JavaScript to calculate results. The form still shows every input and formula
                            description so the method is clear.
                        </div>
                    </noscript>
                </div>

                <aside class="results-panel" aria-labelledby="results-title">
                    <div class="panel-head">
                        <h2 id="results-title">Results</h2>
                        <p>Updated automatically</p>
                    </div>
                    <div class="tool-results" data-tool-results aria-live="polite" aria-atomic="false">
                        <div class="results-empty">
                            <span class="results-empty__icon"><x-icon name="calculator" :size="26" /></span>
                            <p><strong>Your result appears here.</strong></p>
                            <p>Change a value and the calculation updates automatically.</p>
                        </div>
                    </div>
                    <div class="results-tip">
                        <x-icon name="lightbulb" :size="16" />
                        <span>You can bookmark or share this page with your values in the address bar.</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="tool-about">
                <div class="prose">
                    <p class="eyebrow">About this tool</p>
                    <h2>How {{ $tool->name }} works</h2>
                    <p>{{ $tool->description }}</p>
                    <p>
                        The calculator is part of the {{ $tool->category->name }} collection. It follows a fixed,
                        tested method: the same inputs always produce the same result, and invalid combinations are
                        rejected with a clear message rather than a silent zero.
                    </p>
                    <p>
                        Because the calculation happens on your device, it works offline once the page has loaded and
                        keeps sensitive numbers — salaries, health figures, prices — out of server logs.
                    </p>

                    @if (! empty($tool->keywords))
                        <div class="tag-row">
                            @foreach ($tool->keywords as $keyword)
                                <span class="tag">{{ $keyword }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="info-card">
                    <h2>Tool details</h2>
                    <dl>
                        <div><dt>Category</dt><dd><a href="{{ route('tools.index', ['category' => $tool->category->slug]) }}">{{ $tool->category->name }}</a></dd></div>
                        <div><dt>Version</dt><dd>{{ $tool->version }}</dd></div>
                        <div><dt>Inputs</dt><dd>{{ count($tool->inputs) }}</dd></div>
                        <div><dt>Processing</dt><dd>Client-side</dd></div>
                        <div><dt>Account required</dt><dd>No</dd></div>
                    </dl>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->count())
        <section class="section section--tinted">
            <div class="container">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Keep going</p>
                        <h2>Related tools</h2>
                    </div>
                    <a class="text-link" href="{{ route('tools.index', ['category' => $tool->category->slug]) }}">
                        More in {{ $tool->category->name }} <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>
                <div class="tool-grid">
                    @foreach ($related as $relatedTool)
                        <x-tool-card :tool="$relatedTool" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/js/tool-runner.js') }}"></script>
@endpush
