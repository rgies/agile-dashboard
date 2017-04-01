<?php

namespace Rgies\MetricsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\JiraException;


/**
 * Jira controller.
 *
 * @Route("/jira", name="jira")
 */
class JiraController extends Controller
{
    /**
     * @Route("/count/", name="jira-count")
     * @Template()
     */
    public function countAction()
    {
        return array();
    }

    /**
     * @Route("/disableAjax/", name="jira-disable-ajax")
     * @Method("POST")
     * @return Response
     */
    public function disableAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $id = $request->get('id');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widgets entity.');
        }

        $entity->setEnabled(false);
        $em->flush();


        return new Response(json_encode(array()), Response::HTTP_OK);
    }

    /**
     * @Route("/countAjax/", name="jira-count-ajax")
     * @Method("POST")
     * @return Response
     */
    public function countAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $id = $request->get('id');
        $response = array('count' => '###');

        //$jql = 'project = Consumer and "Epic Link" = CON-1070 AND fixVersion = "GMD v1 (MVP)" and status = Done';

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MetricsBundle:Widgets')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Widget entity.');
        }

        $query = $em->getRepository('MetricsBundle:JiraCountConfig')->createQueryBuilder('i')
            ->where('i.widgetId = :id')
            ->setParameter('id', $id);
        $items = $query->getQuery()->getResult();


        if ($items)
        {
            $jql = $items[0]->getJqlQuery();

            $issues = $this->_getIssueList($jql);

            if ($issues) {
                $response['count'] = $issues->getTotal();
            }
        }


        return new Response(json_encode($response), Response::HTTP_OK);
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

    /**
     * Gets issue list from given jql.
     *
     * @param string $jql
     * @param int $limit
     * @return \JiraRestApi\Issue\IssueSearchResult|null
     */
    protected function _getIssueList($jql, $limit = 100000)
    {
        $ret = null;

        try {
            $issueService = new IssueService($this->_getLoginCredentials());

            //$ret = $issueService->search($jql, 0, 100, ['null']);
            $ret = $issueService->search($jql, 0, $limit, ['created','resolutiondate']);

            //var_dump($ret);
        } catch (JiraException $e) {
            $this->createNotFoundException('Search Failed: ' . $e->getMessage());
        }

        return $ret;
    }

}
