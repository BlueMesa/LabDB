<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new KULeuven\ShibbolethBundle\ShibbolethBundle(),
            new VIB\SiteTemplateBundle\VIBSiteTemplateBundle(),
            new VIB\FormsBundle\VIBFormsBundle(),
            new VIB\FliesBundle\VIBFliesBundle(),
            new VIB\SecurityBundle\VIBSecurityBundle(),
            new VIB\UserBundle\VIBUserBundle(),
            new VIB\WelcomeBundle\VIBWelcomeBundle(),
            new VIB\CalendarBundle\VIBCalendarBundle(),
            new VIB\TestBundle\VIBTestBundle(),
            new VIB\AntibodyBundle\VIBAntibodyBundle(),
            new VIB\SearchBundle\VIBSearchBundle(),
            new Bluemesa\Bundle\CoreBundle\BluemesaCoreBundle(),
            new Bluemesa\Bundle\AclBundle\BluemesaAclBundle(),
            new Bluemesa\Bundle\StorageBundle\BluemesaStorageBundle(),
            new VIB\ImapUserBundle\VIBImapUserBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
