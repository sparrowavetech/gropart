<?php 

namespace Botble\ContentInjector\Http\Middleware;

use Botble\ContentInjector\Models\ContentInjector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ContentInjectorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // admin and api found in the url, so we will not inject content
        if (strpos($request->url(), 'admin') !== false || strpos($request->url(), 'api') !== false) {
            return $next($request);
        }
        
        $response = $next($request);    

        $contentVariable = ContentInjector::where('status', "published")->select("name", "value")->get();
        if(!empty($contentVariable)){
            if($response instanceof \Illuminate\Http\Response) {
                $content = $response->getContent();
                foreach($contentVariable as $var){
                    $variable = $var->name;
                    $value = $var->value;
                    $content = str_replace($variable, $value, $content);
                }  
                $response->setContent($content);
            }
        }

        return $response;
    }

    public function terminate($request, $response)
    {
        // Called after the response has been sent to the browser
    }
}
