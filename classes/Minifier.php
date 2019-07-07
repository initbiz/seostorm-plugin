<?php namespace Arcane\Seo\Classes;

use tubalmartin\CssMin\Minifier as CssMin;
use Arcane\Seo\Models\Settings;
use voku\helper\HtmlMin;

class Minifier {
    static function minifyJs (string $url) {
        $isDev = env('ENV') === "dev";
        $settings = Settings::instance();
        
        $path = parse_url($url, PHP_URL_PATH);
        $jsContent = \File::get($_SERVER['DOCUMENT_ROOT'].$path);
        $miniJsPath = 'arcane/seo/minify/js'.$path;

        if (!self::isMinifyEnabled('js')) return $url;

        if (! \Storage::exists($miniJsPath)) {
            $miniJs = \JShrink\Minifier::minify($jsContent);
            \Storage::put($miniJsPath, $miniJs);
        }

        return url( \Storage::url($miniJsPath) );
    }
    
    static function minifyCss (string $url) {
        
        $path = parse_url($url, PHP_URL_PATH);
        $input_css = \File::get($_SERVER['DOCUMENT_ROOT'].$path);
        $miniCssPath = 'arcane/seo/minify/css'.$path;
        
        if (!self::isMinifyEnabled('css')) return $url;

        if (! \Storage::exists($miniCssPath)) {
            $compressor = new CssMin;
            // Remove important comments from output.
            $compressor->removeImportantComments();
            // Compress the CSS code!
            $output_css = $compressor->run($input_css);

          \Storage::put($miniCssPath, $output_css);
        }

        return url(\Storage::url($miniCssPath) );
    }

    static function minifyHtml ($content) {
       
        if (!self::isMinifyEnabled('html')) return $content;

        $htmlMin = new HtmlMin();
        $htmlMin->doOptimizeViaHtmlDomParser(true);           // optimize html via "HtmlDomParser()"
        $htmlMin->doRemoveComments();                         // remove default HTML comments (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doSumUpWhitespace();                        // sum-up extra whitespace from the Dom (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doRemoveWhitespaceAroundTags();             // remove whitespace around tags (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doOptimizeAttributes(true);                 // optimize html attributes (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doRemoveHttpPrefixFromAttributes();         // remove optional "http:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDefaultAttributes();                // remove defaults (depends on "doOptimizeAttributes(true)" | disabled by default)
        $htmlMin->doRemoveDeprecatedAnchorName();             // remove deprecated anchor-jump (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedScriptCharsetAttribute(); // remove deprecated charset-attribute - the browser will use the charset from the HTTP-Header, anyway (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedTypeFromScriptTag();      // remove deprecated script-mime-types (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink(); // remove "type=text/css" for css links (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveEmptyAttributes();                  // remove some empty attributes (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveValueFromEmptyInput();              // remove 'value=""' from empty <input> (depends on "doOptimizeAttributes(true)")

        return $htmlMin->minify($content);
    }


    static function isMinifyEnabled($type) {
        $isDev = env('ENV') === "dev";
        $settings = Settings::instance();
        $contentEnabled = false;

        switch($type) {
            case 'html':
                $contentEnabled = $settings->minify_html;
                break;
            case 'js':
                $contentEnabled = $settings->minify_js;
                break;
            case 'css':
                $contentEnabled = $settings->minify_css;
                break;
        }

        return 
            $contentEnabled && 
            !$settings->no_minify_for_dev && !$isDev || 
            !$settings->no_minify_for_dev && $isDev || 
            $settings->no_minify_for_dev && !$isDev
        ;
    }

}