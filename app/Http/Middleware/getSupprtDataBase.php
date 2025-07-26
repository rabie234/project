<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class getSupprtDataBase
{
     /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Example: Determine the database based on the request's subdomain
        // $subdomain = explode('.', $request->getHost())[0];
        $databaseConfig = $this->getDatabaseConfig();

        // Set the database connection
        Config::set('database.connections.mysql', $databaseConfig);

        return $next($request);
    }





        /**
     * Get the database configuration for a given subdomain.
     *
     * @param  string  $subdomain
     * @return array
     */
    protected function getDatabaseConfig()
    {
        // Example configuration array; replace with your logic to fetch the config
        return [
            'driver' => 'mysql',
            'host' => '50.87.144.160',
            'port' => '3306',
            'database' => 'softprom_support',
            'username' => 'softprom_support',
            'password' => 'Support@123&*',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
        
    }
}
