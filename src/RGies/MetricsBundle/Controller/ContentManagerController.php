<?php

namespace RGies\MetricsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use RGies\MetricsBundle\Entity\CmsContent;

class ContentManagerController extends Controller
{
    /**
     * Saves page cms content.
     *
     * @Route("/cm-save-content", name="cmSaveContent")
     * @Method("POST")
     * @Template()
     */
    public function saveContentAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $id = $request->request->get('id');
        $secret  = $this->container->getParameter('secret');
        $content = $request->request->get('content');
        $author  = $request->request->get('author', "");
        $locale  = $request->request->get('locale', "en");
        $token   = $request->request->get('token');
        $random  = substr($token, -2);

        $matchToken = md5($author . $locale . $secret . $random) . $random;

        if ($token != $matchToken)
        {
            return new Response('Access not allowed', Response::HTTP_FORBIDDEN);
        }

        $entity = new CmsContent();
        $entity->setToken($id);
        $entity->setContent($content);
        $entity->setCreatedate(time());
        $entity->setAuthor($author);
        $entity->setLocale($locale);

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        // garbage collection to the newest 200 entries
        //@todo: add garbage collection here

        return new Response('success', Response::HTTP_OK);
    }

    /**
     * Delete images from image library.
     *
     * @Route("/cm-images-delete", name="cmImageDelete")
     * @Method("POST")
     * @Template()
     */
    public function imageDeleteAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $secret  = $this->container->getParameter('secret');
        $filename = $request->get('filename');
        $token = $request->get('token');

        if (md5($filename . $secret) != $token)
        {
            return new Response('No valid token', Response::HTTP_FORBIDDEN);
        }

        if (is_file($this->_getImageLibraryPath() . '/' . basename($filename)))
        {
            unlink($this->_getImageLibraryPath() . '/' . basename($filename));
        }

        return new Response('success', Response::HTTP_OK);
    }

    /**
     * Gets images from image library.
     *
     * @Route("/cm-images-upload", name="cmImageLibrary")
     * @Method("POST")
     * @Template()
     */
    public function imageUploadAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        // check for valid token
        $author  = $request->headers->get('author', "");
        $token  = $request->headers->get('token', "");
        $secret  = $this->container->getParameter('secret');
        $random  = substr($token, -2);

        $matchToken = md5($author . $secret . $random) . $random;

        if ($token != $matchToken)
        {
            return new Response('Access not allowed', Response::HTTP_FORBIDDEN);
        }

        $allowedFileTypes = array('.png', '.jpg', '.gif');

        foreach ($request->files as $file)
        {
            $uploadFile = $this->_getImageLibraryPath() . '/' . basename($file->getClientOriginalName());

            $ext = substr($file->getClientOriginalName(), -4);

            if (!in_array($ext, $allowedFileTypes))
            {
                continue;
            }

            if (!move_uploaded_file($file->getPathname(), $uploadFile))
            {
                return new Response('Error on image upload', Response::HTTP_OK);
            }

            // check if real image file
            if (!$size = @getimagesize($uploadFile))
            {
                @unlink($uploadFile);
            }
        }

        return new Response('success', Response::HTTP_OK);
    }

    /**
     * Gets images from image library.
     *
     * @Route("/cm-get-image-drop-zone", name="cmGetImageDropZone")
     * @Method("POST")
     * @Template()
     */
    public function imageDropZoneAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            die('No valid request');
        }

        return array();    }

    /**
     * Gets images from image library.
     *
     * @Route("/cm-get-images-from-library", name="cmGetImageLibrary")
     * @Method("POST")
     * @Template()
     */
    public function imageLibraryAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            die('No valid request');
        }

        $uploadUrl = $this->generateUrl('cmImageLibrary');

        $output = array();
        $imageUrl = $this->_getImageLibraryUrl();
        $imagePath = $this->_getImageLibraryPath();
        $secret  = $this->container->getParameter('secret');

        if (is_dir($imagePath))
        {
            if ($files = scandir($imagePath))
            {
                foreach ($files as $file)
                {
                    if ($file[0] != '.' && is_file($imagePath . '/' . $file))
                    {
                        if (!$size = @getimagesize($imagePath . '/' . $file))
                        {
                            // jump over if no image
                            continue;
                        }

                        $item = array('src' => $imageUrl . '/' . $file);

                        // calculate icon size 80x80 px
                        $iconSize = 70;
                        $width = $iconSize;
                        if (!isset($size[0]) || !isset($size[0]) || $size[0]==0 || $size[1]==0)
                        {
                            $height = $iconSize;
                        }
                        else
                        {
                            $height = intval($width / $size[0] * $size[1]);
                            if ($size[1] > $size[0])
                            {
                                $height = $iconSize;
                                $width = intval($height / $size[1] * $size[0]);
                            }
                        }

                        $item['id'] = md5($file);
                        $item['width'] = $width;
                        $item['height'] = $height;
                        $item['filename'] = $file;
                        $item['token'] = md5($file . $secret);

                        $output[$file] = $item;
                    }
                }
            }
        }

        asort($output);

        return array('items' => $output, 'upload_url' => $uploadUrl);    }

    /**
     * Gets the path to images library.
     */
    protected function _getImageLibraryPath()
    {
        $imgFolder = 'uploads/img-library';
        return $this->get('kernel')->getRootDir() . '/../web/' . $imgFolder;
    }

    /**
     * Gets the url to image library.
     */
    protected function _getImageLibraryUrl()
    {
        $imgFolder = 'uploads/img-library';
        return $this->container->get('templating.helper.assets')->getUrl($imgFolder);
    }
}
