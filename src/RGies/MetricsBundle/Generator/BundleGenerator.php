<?php

/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\GuiBundle\Generator
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-06
 */

namespace RGies\MetricsBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates a bundle.
 *
 * @author Robert Gies <mail@rgies.com>
 */
class BundleGenerator extends Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate($namespace, $bundle, $dir, $format, $structure, $templateDir)
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($bundle, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'format'    => $format,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );


        $this->setSkeletonDirs(__DIR__ . '/../Resources/SensioGeneratorBundle/skeleton');

        $this->renderFile('bundle/Bundle.php.twig', $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile('bundle/Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', $parameters);
        $this->renderFile('bundle/Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('bundle/DefaultController.php.twig', $dir.'/Controller/DefaultController.php', $parameters);
        $this->renderFile('bundle/DefaultControllerTest.php.twig', $dir.'/Tests/Controller/DefaultControllerTest.php', $parameters);
        $this->renderFile('bundle/index.html.twig.twig', $dir.'/Resources/views/Default/index.html.twig', $parameters);
        $this->renderFile('bundle/services.yml.twig', $dir.'/Resources/config/services.yml', $parameters);


        $this->renderFile('bundle/messages.fr.xlf', $dir.'/Resources/translations/messages.fr.xlf', $parameters);

        $this->filesystem->mkdir($dir.'/Resources/doc');
        $this->filesystem->touch($dir.'/Resources/doc/index.rst');
        $this->filesystem->mkdir($dir.'/Resources/translations');
        $this->filesystem->mkdir($dir.'/Resources/public/css');
        $this->filesystem->mkdir($dir.'/Resources/public/images');
        $this->filesystem->mkdir($dir.'/Resources/public/js');

        // copy bundle template files
        if ($templateDir)
        {
            // copy controller
            $this->_copyBundleTemplates($dir . '/Controller',
                $templateDir . '/controller', $parameters);

            // copy views
            $this->_copyBundleTemplates($dir . '/Resources/views',
                $templateDir . '/views', $parameters);

            // copy css
            $this->_copyBundleTemplates($dir . '/Resources/public/css',
                $templateDir . '/css', $parameters);

            // copy js
            $this->_copyBundleTemplates($dir . '/Resources/public/js',
                $templateDir . '/js', $parameters);

            // copy images
            $this->_copyBundleTemplates($dir . '/Resources/public/images',
                $templateDir . '/images', $parameters, true);

            // copy skeleton
            $this->_copyBundleTemplates($dir . '/Resources/SensioGeneratorBundle/skeleton',
                $templateDir . '/skeleton', $parameters, true);

            // copy twig extensions
            $this->_copyBundleTemplates($dir . '/Twig',
                $templateDir . '/twig', $parameters);

            // copy service configs
            $this->_copyBundleTemplates($dir . '/Resources/config',
                $templateDir . '/config', $parameters);

            // copy entities
            $this->_copyBundleTemplates($dir . '/Entity',
                $templateDir . '/entity', $parameters);

            // copy services
            $this->_copyBundleTemplates($dir . '/Services',
                $templateDir . '/services', $parameters);

            // copy forms
            $this->_copyBundleTemplates($dir . '/Form',
                $templateDir . '/form', $parameters);
        }
    }

    protected function _copyBundleTemplates($targetDir, $templateDir, $parameters, $onlyCopy = false)
    {
        if (is_dir($templateDir))
        {
            if ($files = scandir($templateDir))
            {
                foreach ($files as $file)
                {
                    if ($file[0] == '.') continue;

                    if (is_dir($templateDir . '/' . $file))
                    {
                        $this->_copyBundleTemplates(
                            $targetDir . '/' . $file, $templateDir . '/' . $file, $parameters, $onlyCopy
                        );
                    }
                    elseif (substr($file, -5) == '.twig' && !$onlyCopy)
                    {
                        $this->setSkeletonDirs($templateDir);
                        $this->renderFile($file, $targetDir . '/' . basename($file, '.twig'), $parameters);
                    }
                    else
                    {
                        $this->filesystem->copy($templateDir . '/' . $file, $targetDir . '/' . $file);
                    }
                }
            }
        }
    }
}
