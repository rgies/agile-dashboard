<?php

namespace RGies\JiraListWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\TimeTracking;
use JiraRestApi\JiraException;


/**
 * Widget controller.
 *
 * @Route("/jira_list_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraListWidgetBundle-collect-data")
     * @Method("POST")
     * @return Response
     */
    public function collectDataAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $widgetId       = $request->get('id');
        $widgetType     = $request->get('type');

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraListWidgetBundle', $widgetId)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $widgetConfig = $this->get('WidgetService')->getWidgetConfig($widgetType, $widgetId);

        $response = array();

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($this->_getLoginCredentials());
            $issues = $issueService->search($jql, 0, 5, ['key', 'summary']);
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        $value = 'NO ITEMS FOUND';
        if ($issues) {
            $value = '<table>';

            foreach ($issues->getIssues() as $issue) {
                $value = $value . '<tr><td><i class="fa fa-circle"></i> '
                    . $this->_createLink($issue->key,$issue->fields->summary)
                    . '&nbsp;&nbsp;</td><td>'
                    . '<i><span title="' . $issue->fields->summary . '">' . substr($issue->fields->summary, 0, 12)
                    . '</span></i></td></tr>';
            }
            $value .= '</table>';
        }

        $response['value'] = $value;

        // Cache response data
        $cache->setValue('JiraListWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    protected function _createLink($key, $text)
    {
        return '<a href="' . $this->getParameter('jira_host') . '/browse/' . $key
            . '" target="_blank" title="' . $text . '">' . $key . '</a>';
    }


    /**
     * @return ArrayConfiguration
     */
    protected function _getLoginCredentials()
    {
        return new ArrayConfiguration(
            array(
                'jiraHost' => $this->getParameter('jira_host'),
                'jiraUser' => $this->getParameter('jira_user'),
                'jiraPassword' => $this->getParameter('jira_password'),
            )
        );
    }
}
