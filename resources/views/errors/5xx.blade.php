<x-errors.page :code="isset($exception) ? $exception->getStatusCode() : 500" :exception="$exception ?? null" />
