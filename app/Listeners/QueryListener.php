<?php

namespace App\Listeners;

use IlluminateDatabaseEventsQueryExecuted;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Logs\FileLogger;
class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  IlluminateDatabaseEventsQueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        if(env('DB_SQL_LOG'))
        {
            $sql  = str_replace("?", '"%s"', $event->sql);
             //$sql  = vsprintf($sql, $event->bindings);
            $sql = order_to_replace('/%s/', $sql, $event->bindings);
            $time = $event->time;
            $name = $event->connectionName;
            $data = compact('sql','time','name');
            FileLogger::getLogger(FileLogger::LOG_QUERY_REAL_TIME)->info($data);
        }
    }
}
