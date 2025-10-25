<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Create a new middleware instance.
     */
    public function __construct()
    {
        // Trust all proxies if enabled
        if (env('TRUST_ALL_PROXIES', false)) {
            $this->proxies = '*';
        }
        // Trust specific proxies
        elseif (env('TRUST_PROXIES')) {
            $this->proxies = explode(',', env('TRUST_PROXIES'));
        }
        // Default: trust common proxy IPs
        else {
            $this->proxies = [
                '127.0.0.0/8',    // localhost
                '10.0.0.0/8',     // private network
                '172.16.0.0/12',  // private network
                '192.168.0.0/16', // private network
            ];
        }

        // Configure headers from env
        if (env('TRUST_HEADERS')) {
            $headers = explode(',', env('TRUST_HEADERS'));
            $this->headers = 0;
            
            foreach ($headers as $header) {
                $header = trim($header);
                switch ($header) {
                    case 'HEADER_X_FORWARDED_FOR':
                        $this->headers |= Request::HEADER_X_FORWARDED_FOR;
                        break;
                    case 'HEADER_X_FORWARDED_HOST':
                        $this->headers |= Request::HEADER_X_FORWARDED_HOST;
                        break;
                    case 'HEADER_X_FORWARDED_PORT':
                        $this->headers |= Request::HEADER_X_FORWARDED_PORT;
                        break;
                    case 'HEADER_X_FORWARDED_PROTO':
                        $this->headers |= Request::HEADER_X_FORWARDED_PROTO;
                        break;
                    case 'HEADER_X_FORWARDED_AWS_ELB':
                        $this->headers |= Request::HEADER_X_FORWARDED_AWS_ELB;
                        break;
                }
            }
        }
    }
}
