<?php

/* layout/default.twig */
class __TwigTemplate_6e46f3aef1518b66c7584c10aa806a5311a3ba512ea1d2264f920916ee093a36 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        if (((isset($context["part"]) ? $context["part"] : null) == "header")) {
            // line 2
            echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        
        <link href=\"<?php echo assetpath('css/default.css'); ?>\" rel=\"stylesheet\" type=\"text/css\" />
        
        <script>var ROOT_PATH = '<?php echo path(); ?>';</script>
        <script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js\"></script>
        <script src=\"<?php echo assetpath('js/functions.js'); ?>\"></script>

        <title>Clockwork Framework</title>
    </head>
    <body>
    
        <div id=\"wrapper\">
";
        } elseif (((isset($context["part"]) ? $context["part"] : null) == "footer")) {
            // line 19
            echo "        </div>
    
    </body>
</html>
";
        }
    }

    public function getTemplateName()
    {
        return "layout/default.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  40 => 19,  21 => 2,  19 => 1,);
    }
}
