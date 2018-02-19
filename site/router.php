<?php
defined('_JEXEC') or die;
/**
 * Joomla component com_gginterface
 *
 * @package WebTV
 * Router.php
 */
class gginterfaceRouter extends JComponentRouterBase
{

    function build(&$query) {
        $segments = array();

        if (isset($query['view'])) {
            $segments[] = $query['view'];
            unset($query['view']);
        }

        if (isset($query['id'])) {
            $segments[] = $query['id'];
            unset($query['id']);
        }

        if (isset($query['type'])) {
            $segments[] = $query['type'];
            unset($query['type']);
        }


        return $segments;
    }

    function parse(&$segments) {
        $db = JFactory::getDbo();
        $vars = array();

        switch ($segments[0]) {

            default:
                $vars['view'] = 'gginterface';
                break;

        }
        return $vars;
    }
}
function gginterfaceBuildRoute(&$query)
{

    $router = new gginterfaceRouter;
    return $router->build($query);
}

function gginterfaceParseRoute($segments)
{
    $router = new gginterfaceRouter;

    return $router->parse($segments);
}


