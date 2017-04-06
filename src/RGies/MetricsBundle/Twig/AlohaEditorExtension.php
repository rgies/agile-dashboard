<?php
/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-04
 */

namespace RGies\MetricsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Ahoha Text Editor Twig Extension.
 *
 * @package Metrics\MetricsBundle
 */
class AlohaEditorExtension extends \Twig_Extension
{
    private $container;
    private $generator;
    private $doctrine;

    public function __construct(ContainerInterface $container, UrlGeneratorInterface $generator, RegistryInterface $doctrine)
    {
        $this->container = $container;
        $this->generator = $generator;
        $this->doctrine = $doctrine;
    }

    public function getFunctions()
    {
        return array(
            'editor_init'       => new \Twig_SimpleFunction('editor_init', array($this, 'initEditor'), array('is_safe' => array('html'))),
            'editor_content'    => new \Twig_SimpleFunction('editor_get_content', array($this, 'getContent'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Gets the saved content by given id.
     */
    public function getContent($id, $default='')
    {
        $locale = $this->container->get('request')->getLocale();

        $em = $this->doctrine->getManager();
        $entities = $em->getRepository('MetricsBundle:CmsContent')->findBy(
            array('token' => $id, 'locale' => $locale),
            array('id' => 'DESC'),
            1
        );

        if (count($entities))
        {
            return $entities[0]->getContent();
        }

        return $default;
    }

    /**
     * Inits the aloha content editor.
     */
    public function initEditor()
    {
        $request   = $this->container->get('request');
        $user      = $this->container->get('security.context')->getToken()->getUser();
        $pathAlCss = $this->container->get('templating.helper.assets')->getUrl('js/aloha/css/aloha.css');
        $pathAlJs  = $this->container->get('templating.helper.assets')->getUrl('js/aloha/lib/aloha-full.min.js');
        $pathAlExtJs = $this->container->get('templating.helper.assets')->getUrl('bundles/metrics/js/aloha-ext.js');

        // generate security token
        $secret = $this->container->getParameter('secret');
        $random = rand(11, 99);
        $token = md5($user->getUsername() . $request->getLocale() . $secret . $random) . $random;

        $random = rand(11, 99);
        $uploadToken = md5($user->getUsername() . $secret . $random) . $random;

        $savePath = $this->generator->generate('cmSaveContent');
        $imageLibPath = $this->generator->generate('cmGetImageLibrary');
        $dropZonePath = $this->generator->generate('cmGetImageDropZone');
        $uploadPath = $this->generator->generate('cmImageLibrary');
        $deletePath = $this->generator->generate('cmImageDelete');

        // get aloha configuration from bundle service.xml parameters
        $config = $this->container->getParameter('Aloha');
        $plugins = $config['plugins'];

        $ret = '<!-- Aloha Texteditor integration -->
        <link rel="stylesheet" href="' . $pathAlCss . '" type="text/css">

        <script>
            var Aloha = window.Aloha || ( window.Aloha = {} );
            Aloha.settings = { ' . $config['settings'] . ' };
        </script>

        <script src="' . $pathAlJs . '" data-aloha-plugins="' . $plugins . '"></script>
        <script src="' . $pathAlExtJs . '"></script>

        <script>
            Aloha.ready( function() {
                Aloha.jQuery(".editable").aloha();

                Aloha.bind("aloha-smart-content-changed", function(event, editable) {
                    $.post( "' . $savePath . '",
                        {
                            id: editable.editable.getId(),
                            author: "' . $user->getUsername() . '",
                            locale: "' . $request->getLocale() . '",
                            content: editable.editable.getContents(),
                            token: "' . $token . '"
                        }).done(function( data ) {});
               });

                aloha_ext_init();

                function insertImageAtCursor(src) {
                    var sel, range;

                    if (window.getSelection) {
                        sel = window.getSelection();

                        if (sel.getRangeAt && sel.rangeCount) {
                            range = sel.getRangeAt(0);
                            range.deleteContents();
                            var img = new Image();
                            img.src = src;
                            range.insertNode(img);
                        }
                    } else if (document.selection && document.selection.createRange) {
                        var html = \'<img src="\' + src + \'"/>\';
                        document.selection.createRange().pasteHTML(html);
                    }
                }

                $.post("' . $dropZonePath . '",
                        {
                            author: "' . $user->getUsername() . '",
                            locale: "' . $request->getLocale() . '",
                            token: "' . $token . '"
                        }
                ).done(function( data ) {
                    $("#aloha-image-upload").html(data);

                    var myDropzone = new Dropzone("#image-upload-form", {
                        url: "' . $uploadPath . '",
                        previewsContainer: "#dz-details",
                        headers: {"token":"' . $uploadToken . '", "author":"' . $user->getUserName() . '"},
                        success: function() { refreshImageLibrary(); }
                    });

                    refreshImageLibrary();

                    $(document).on("click", "#cm-img-item", function(event){
                        if (Aloha.activeEditable != null)
                        {
                            Aloha.activeEditable.obj.context.focus()
                            insertImageAtCursor($(this).context.src);
                        }
                    });

                    $(document).on("click", ".img-lib-del-button", function(event){
                        if (confirm("Delete Image [" + $("#" + $(this).attr("id") + "-form input:first").val() + "] ?")) {
                            event.preventDefault();

                            $.post("' . $deletePath . '", $( "#" + $(this).attr("id") + "-form" ).serialize())
                            .done(function( data ) {
                                refreshImageLibrary();
                            });
                        }
                    });
                });

                function refreshImageLibrary()
                {
                    $("#aloha-image-library").html(\'<div style="text-align:center">Loading ...</div>\');

                    $.post("' . $imageLibPath . '",
                            {
                                author: "' . $user->getUsername() . '",
                                locale: "' . $request->getLocale() . '",
                                token: "' . $token . '"
                            }
                    ).done(function( data ) {
                                $("#aloha-image-library").html(data);
                            });
                }

            });
        </script>';
        return $ret;
    }

    public function getName()
    {
        return 'alohaeditor_extension';
    }
}