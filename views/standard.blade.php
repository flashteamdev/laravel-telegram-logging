{!! Str::limit($message, 2000) !!}
@if (!empty($context))
  <pre>{{ json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
@endif
@if (!empty($exception))
  <pre><code class="language-log">{{ $exception }}</code></pre>
@endif
