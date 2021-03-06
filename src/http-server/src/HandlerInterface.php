<?php

namespace Mix\Http\Server;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Interface HandlerInterface
 * @package Mix\Http\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
interface HandlerInterface
{

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handleHTTP(ServerRequest $request, Response $response);

}
