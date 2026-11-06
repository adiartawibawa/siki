<x-errors.page :code="isset($exception) ? $exception->getStatusCode() : 400" :exception="$exception ?? null" />
