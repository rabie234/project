<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class SetDatabaseConnection
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
        $dbName = $request->query('db_name');

        if ($dbName) {
            // Set the database connection
            $databaseConfig = $this->getDatabaseConfig($dbName);
            Config::set('database.connections.mysql', $databaseConfig);
        }

        return $next($request);
    }

    /**
     * Get the database configuration for a given subdomain.
     *
     * @param  string  $subdomain
     * @return array
     */
    protected function getDatabaseConfig($dbName)
    {
        // Example configuration array; replace with your logic to fetch the config
        return [
            'driver' => 'mysql',
            'host' => '50.87.144.160',
            'port' => '3306',
            'database' => $dbName,
            'username' => 'softprom_Admin',
            'password' => 'mhD2p@ss',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
        
    }
}
