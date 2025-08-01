<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* console/display.twig */
class __TwigTemplate_bb41491419babd2021b48e1495aad962 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<div id=\"pma_console_container\" class=\"d-print-none\">
    <div id=\"pma_console\">
        ";
        // line 4
        echo "        ";
        $this->loadTemplate("console/toolbar.twig", "console/display.twig", 4)->display(twig_to_array(["parent_div_classes" => "collapsed", "content_array" => [0 => [0 => "switch_button console_switch", 1 => _gettext("Console"), "image" =>         // line 7
($context["image"] ?? null)], 1 => [0 => "button clear", 1 => _gettext("Clear")], 2 => [0 => "button history", 1 => _gettext("History")], 3 => [0 => "button options", 1 => _gettext("Options")], 4 => ((        // line 11
($context["has_bookmark_feature"] ?? null)) ? ([0 => "button bookmarks", 1 => _gettext("Bookmarks")]) : (null)), 5 => [0 => "button debug hide", 1 => _gettext("Debug SQL")]]]));
        // line 15
        echo "        ";
        // line 16
        echo "        <div class=\"content\">
            <div class=\"console_message_container\">
                <div class=\"message welcome\">
                    <span id=\"instructions-0\">
                        ";
echo _gettext("Press Ctrl+Enter to execute query");
        // line 21
        echo "                    </span>
                    <span class=\"hide\" id=\"instructions-1\">
                        ";
echo _gettext("Press Enter to execute query");
        // line 24
        echo "                    </span>
                </div>
                ";
        // line 26
        if ( !twig_test_empty(($context["sql_history"] ?? null))) {
            // line 27
            echo "                    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_reverse_filter($this->env, ($context["sql_history"] ?? null)));
            foreach ($context['_seq'] as $context["_key"] => $context["record"]) {
                // line 28
                echo "                        <div class=\"message history collapsed hide";
                // line 29
                echo ((twig_matches("@^SELECT[[:space:]]+@i", (($__internal_compile_0 = $context["record"]) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["sqlquery"] ?? null) : null))) ? (" select") : (""));
                echo "\"
                            targetdb=\"";
                // line 30
                echo twig_escape_filter($this->env, (($__internal_compile_1 = $context["record"]) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["db"] ?? null) : null), "html", null, true);
                echo "\" targettable=\"";
                echo twig_escape_filter($this->env, (($__internal_compile_2 = $context["record"]) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["table"] ?? null) : null), "html", null, true);
                echo "\">
                            ";
                // line 31
                $this->loadTemplate("console/query_action.twig", "console/display.twig", 31)->display(twig_to_array(["parent_div_classes" => "action_content", "content_array" => [0 => [0 => "action collapse", 1 => _gettext("Collapse")], 1 => [0 => "action expand", 1 => _gettext("Expand")], 2 => [0 => "action requery", 1 => _gettext("Requery")], 3 => [0 => "action edit", 1 => _gettext("Edit")], 4 => [0 => "action explain", 1 => _gettext("Explain")], 5 => [0 => "action profiling", 1 => _gettext("Profiling")], 6 => ((                // line 40
($context["has_bookmark_feature"] ?? null)) ? ([0 => "action bookmark", 1 => _gettext("Bookmark")]) : (null)), 7 => [0 => "text failed", 1 => _gettext("Query failed")], 8 => [0 => "text targetdb", 1 => _gettext("Database"), "extraSpan" => (($__internal_compile_3 =                 // line 42
$context["record"]) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["db"] ?? null) : null)], 9 => [0 => "text query_time", 1 => _gettext("Queried time"), "extraSpan" => ((twig_get_attribute($this->env, $this->source,                 // line 46
$context["record"], "timevalue", [], "array", true, true, false, 46)) ? ((($__internal_compile_4 =                 // line 47
$context["record"]) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["timevalue"] ?? null) : null)) : (_gettext("During current session")))]]]));
                // line 51
                echo "                            <span class=\"query\">";
                echo twig_escape_filter($this->env, (($__internal_compile_5 = $context["record"]) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["sqlquery"] ?? null) : null), "html", null, true);
                echo "</span>
                        </div>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['record'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 54
            echo "                ";
        }
        // line 55
        echo "            </div><!-- console_message_container -->
            <div class=\"query_input\">
                <span class=\"console_query_input\"></span>
            </div>
        </div><!-- message end -->
        ";
        // line 61
        echo "        <div class=\"mid_layer\"></div>
        ";
        // line 63
        echo "        <div class=\"card\" id=\"debug_console\">
            ";
        // line 64
        $this->loadTemplate("console/toolbar.twig", "console/display.twig", 64)->display(twig_to_array(["parent_div_classes" => "", "content_array" => [0 => [0 => "button order order_asc", 1 => _gettext("ascending")], 1 => [0 => "button order order_desc", 1 => _gettext("descending")], 2 => [0 => "text", 1 => _gettext("Order:")], 3 => [0 => "switch_button", 1 => _gettext("Debug SQL")], 4 => [0 => "button order_by sort_count", 1 => _gettext("Count")], 5 => [0 => "button order_by sort_exec", 1 => _gettext("Execution order")], 6 => [0 => "button order_by sort_time", 1 => _gettext("Time taken")], 7 => [0 => "text", 1 => _gettext("Order by:")], 8 => [0 => "button group_queries", 1 => _gettext("Group queries")], 9 => [0 => "button ungroup_queries", 1 => _gettext("Ungroup queries")]]]));
        // line 79
        echo "            <div class=\"content debug\">
                <div class=\"message welcome\"></div>
                <div class=\"debugLog\"></div>
            </div> <!-- Content -->
            <div class=\"templates\">
                ";
        // line 84
        $this->loadTemplate("console/query_action.twig", "console/display.twig", 84)->display(twig_to_array(["parent_div_classes" => "debug_query action_content", "content_array" => [0 => [0 => "action collapse", 1 => _gettext("Collapse")], 1 => [0 => "action expand", 1 => _gettext("Expand")], 2 => [0 => "action dbg_show_trace", 1 => _gettext("Show trace")], 3 => [0 => "action dbg_hide_trace", 1 => _gettext("Hide trace")], 4 => [0 => "text count hide", 1 => _gettext("Count"), "extraSpan" => ""], 5 => [0 => "text time", 1 => _gettext("Time taken"), "extraSpan" => ""]]]));
        // line 95
        echo "            </div> <!-- Template -->
        </div> <!-- Debug SQL card -->
        ";
        // line 97
        if (($context["has_bookmark_feature"] ?? null)) {
            // line 98
            echo "            <div class=\"card\" id=\"pma_bookmarks\">
                ";
            // line 99
            $this->loadTemplate("console/toolbar.twig", "console/display.twig", 99)->display(twig_to_array(["parent_div_classes" => "", "content_array" => [0 => [0 => "switch_button", 1 => _gettext("Bookmarks")], 1 => [0 => "button refresh", 1 => _gettext("Refresh")], 2 => [0 => "button add", 1 => _gettext("Add")]]]));
            // line 107
            echo "                <div class=\"content bookmark\">
                    ";
            // line 108
            echo ($context["bookmark_content"] ?? null);
            echo "
                </div>
                <div class=\"mid_layer\"></div>
                <div class=\"card add\">
                    ";
            // line 112
            $this->loadTemplate("console/toolbar.twig", "console/display.twig", 112)->display(twig_to_array(["parent_div_classes" => "", "content_array" => [0 => [0 => "switch_button", 1 => _gettext("Add bookmark")]]]));
            // line 118
            echo "                    <div class=\"content add_bookmark\">
                        <div class=\"options\">
                            <label>
                                ";
echo _gettext("Label");
            // line 121
            echo ": <input type=\"text\" name=\"label\">
                            </label>
                            <label>
                                ";
echo _gettext("Target database");
            // line 124
            echo ": <input type=\"text\" name=\"targetdb\">
                            </label>
                            <label>
                                <input type=\"checkbox\" name=\"shared\">";
echo _gettext("Share this bookmark");
            // line 128
            echo "                            </label>
                            <button class=\"btn btn-primary\" type=\"submit\" name=\"submit\">";
echo _gettext("OK");
            // line 129
            echo "</button>
                        </div> <!-- options -->
                        <div class=\"query_input\">
                            <span class=\"bookmark_add_input\"></span>
                        </div>
                    </div>
                </div> <!-- Add bookmark card -->
            </div> <!-- Bookmarks card -->
        ";
        }
        // line 138
        echo "        ";
        // line 139
        echo "        <div class=\"card\" id=\"pma_console_options\">
            ";
        // line 140
        $this->loadTemplate("console/toolbar.twig", "console/display.twig", 140)->display(twig_to_array(["parent_div_classes" => "", "content_array" => [0 => [0 => "switch_button", 1 => _gettext("Options")], 1 => [0 => "button default", 1 => _gettext("Set default")]]]));
        // line 147
        echo "            <div class=\"content\">
                <label>
                    <input type=\"checkbox\" name=\"always_expand\">";
echo _gettext("Always expand query messages");
        // line 150
        echo "                </label>
                <br>
                <label>
                    <input type=\"checkbox\" name=\"start_history\">";
echo _gettext("Show query history at start");
        // line 154
        echo "                </label>
                <br>
                <label>
                    <input type=\"checkbox\" name=\"current_query\">";
echo _gettext("Show current browsing query");
        // line 158
        echo "                </label>
                <br>
                <label>
                    <input type=\"checkbox\" name=\"enter_executes\">
                        ";
echo _gettext("Execute queries on Enter and insert new line with Shift+Enter. To make this permanent, view settings.");
        // line 165
        echo "                </label>
                <br>
                <label>
                    <input type=\"checkbox\" name=\"dark_theme\">";
echo _gettext("Switch to dark theme");
        // line 169
        echo "                </label>
                <br>
            </div>
        </div> <!-- Options card -->
        <div class=\"templates\">
            ";
        // line 175
        echo "            ";
        $this->loadTemplate("console/query_action.twig", "console/display.twig", 175)->display(twig_to_array(["parent_div_classes" => "query_actions", "content_array" => [0 => [0 => "action collapse", 1 => _gettext("Collapse")], 1 => [0 => "action expand", 1 => _gettext("Expand")], 2 => [0 => "action requery", 1 => _gettext("Requery")], 3 => [0 => "action edit", 1 => _gettext("Edit")], 4 => [0 => "action explain", 1 => _gettext("Explain")], 5 => [0 => "action profiling", 1 => _gettext("Profiling")], 6 => ((        // line 184
($context["has_bookmark_feature"] ?? null)) ? ([0 => "action bookmark", 1 => _gettext("Bookmark")]) : (null)), 7 => [0 => "text failed", 1 => _gettext("Query failed")], 8 => [0 => "text targetdb", 1 => _gettext("Database"), "extraSpan" => ""], 9 => [0 => "text query_time", 1 => _gettext("Queried time"), "extraSpan" => ""]]]));
        // line 190
        echo "        </div>
    </div> <!-- #console end -->
</div> <!-- #console_container end -->
";
    }

    public function getTemplateName()
    {
        return "console/display.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  230 => 190,  228 => 184,  226 => 175,  219 => 169,  213 => 165,  206 => 158,  200 => 154,  194 => 150,  189 => 147,  187 => 140,  184 => 139,  182 => 138,  171 => 129,  167 => 128,  161 => 124,  155 => 121,  149 => 118,  147 => 112,  140 => 108,  137 => 107,  135 => 99,  132 => 98,  130 => 97,  126 => 95,  124 => 84,  117 => 79,  115 => 64,  112 => 63,  109 => 61,  102 => 55,  99 => 54,  89 => 51,  87 => 47,  86 => 46,  85 => 42,  84 => 40,  83 => 31,  77 => 30,  73 => 29,  71 => 28,  66 => 27,  64 => 26,  60 => 24,  55 => 21,  48 => 16,  46 => 15,  44 => 11,  43 => 7,  41 => 4,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "console/display.twig", "/home/wp_q3unnj/betsierivercanoesandcampgroundcom.stage.site/wp-content/plugins/wp-phpmyadmin-extension/lib/phpMyAdmin_TuwErYUkJDe6mo2fPj1qzC5/templates/console/display.twig");
    }
}
