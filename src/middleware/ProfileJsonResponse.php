<?php

namespace Larapackages\ProfileJsonResponse\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Closure;

class ProfileJsonResponse
{
    /**
     * limit the profiling data
     *
     * available keys:
     *   __meta
     *  php
     *  messages
     *  time
     *  memory
     *  exceptions
     *  views
     *  route
     *  queries
     *  swiftmailer_mails
     *  auth
     *  gate
     *  session
     *  request
     *
     * leave empty for show all data
     * @var array
     */
    protected $profilingData = [];
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!app()->bound('debugbar') || !app('debugbar')->isEnabled()) {
            return $response;
        }

        if ($response instanceof JsonResponse && $response->has('profile')) {
            $data = $response->getData();
            if (is_array($data)) {
                $response->setData(array_merge($data, [
                    '_debugbar' => $this->getProfilingData()
                ]));
            } else {
                $data->_debugBar = $this->getProfilingData();
                $response->setData($data);
            }
        }

        return $response;
    }

    /**
     * Get profiling data
     *
     * @return array
     */
    protected function getProfilingData()
    {
        if (empty($this->profilingData)) {
            return app('debugbar')->getData();
        }

        return array_only(app('debugbar')->getData(), $this->profilingData);
    }
}
