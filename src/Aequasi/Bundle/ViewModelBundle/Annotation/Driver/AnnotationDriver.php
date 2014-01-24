<?php

namespace Aequasi\Bundle\ViewModelBundle\Annotation\Driver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Aequasi\Bundle\ViewModelBundle\Annotation\ViewModel;
use Aequasi\Bundle\ViewModelBundle\Service\ViewModelService;
use Aequasi\Bundle\ViewModelBundle\View\Model\ViewModelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AnnotationDriver
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var ViewModelService
     */
    protected $viewModelService;

    public function __construct(ContainerInterface $container)
    {
        $this->container        = $container;
        $this->reader           = $container->get('annotation_reader');
        $this->templating       = $container->get('templating');
        $this->viewModelService = $container->get('aequasi.view_model.service.view');
    }

    public function onKernelController(FilterControllerEvent $event)
    {

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof ViewModel) {
                if (isset($configuration->class)) {
                    $class = $configuration->class;
                    if (!class_exists($class)) {
                        $bundle   = $this->getBundleName($object);
                        $oldClass = $class;
                        $class    = sprintf('%s\View\Model\%s', $bundle, $class);
                        if (!class_exists($class)) {
                            throw new \InvalidArgumentException(sprintf(
                                "Neither `%s`, nor `%s` are valid classes. Make sure you use the whole name of the model, or that its placed in your bundle's `View\\Model\\` directory",
                                $oldClass,
                                $class
                            ));
                        }
                    }

                    $this->viewModelService->setViewModel(new $class($this->templating));
                }

                if (isset($configuration->service)) {
                    $service = $this->getService($configuration->service);
                    $this->viewModelService->setViewModel($service);
                }

            }
        }
    }

    /**
     * @param $id
     *
     * @return ViewModelInterface
     */
    private function getService($id)
    {
        return $this->container->get($id);
    }

    private function getBundleName(\ReflectionObject $controller)
    {
        return str_replace('\Controller', '', $controller->getNamespaceName());
    }
}
