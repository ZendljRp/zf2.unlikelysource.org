<?php
namespace CheckOrder;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleEvent;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use CheckOrder\Entity\Storage;
use ReflectionObject;

class Module
{
    // active during module loading stage
	public function init(ModuleManager $mm)
	{
        $eventManager = $mm->getEventManager();
		Storage::$order[] = '======== CheckOrder\Module::init() ===========================';
		Storage::$order[] = '....init() Param: ' . get_class($mm);
		Storage::$order[] = '....Module Event Class: ' . get_class($mm->getEvent());
		Storage::$order[] = '....Module Manager Class: ' . get_class($mm);
		// This is OK
		$eventManager->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, 
							  array($this, 'onLoadModulesPostFromInit'));
	    // NOTE: module manager $mm will only handle module events
	    $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatchFromInit'));
	    // attach custom event listener using shared event manager
	    $sharedEventManager = $eventManager->getSharedManager();
	    $sharedEventManager->attach('sharedInit', 'customEventInit', function ($e) { 
			Storage::$order[] = '....------------ CheckOrder\Module::onLoadModulesPostFromInitShared() -------------------';
			Storage::$order[] = '....Param: ' . get_class($e);
		    });
        Storage::$order[] = '======== List of All Events From init() ==================';
        Storage::$order[] = \Zend\Debug\Debug::dump($eventManager->getEvents(), NULL, FALSE);
	}
	// active during the MVC stage (*after* modules are loaded)
	public function onBootstrap(MvcEvent $e)
	{
        $eventManager = $e->getApplication()->getEventManager();
		Storage::$order[] = '======== CheckOrder\Module::onBootstrap() ===================';
		Storage::$order[] = '....onBootstrap() Param: ' . get_class($e);
		Storage::$order[] = '....MVC Event Class: ' . get_class($e);
		Storage::$order[] = '....MVC Manager Class: ' . get_class($e->getApplication());
		// NOTE: MvcEvent $e will only handle MVC events (to late!!!)
		$eventManager->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, 
							  array($this, 'onLoadModulesPostFromBootstrap'));
	    // This is OK
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatchFromBootstrap'));
		// This is will not work: too late!!!
        $eventManager->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'onDispatchFromBootstrapWontWork'));
	    // attach custom event listener using shared event manager
        $sharedEventManager = $eventManager->getSharedManager();
	    $sharedEventManager->attach('sharedOnBootstrap', 'customEventBootstrap', function ($e) {
			Storage::$order[] = '....------------ CheckOrder\Module::onLoadModulesPostFromBootstrapShared() -------------------';
			Storage::$order[] = '....Param: ' . get_class($e);
		    });
        Storage::$order[] = '======== List of All Events From onBootstrap() =============';
        Storage::$order[] = \Zend\Debug\Debug::dump($eventManager->getEvents(), NULL, FALSE);
	}
	
	public function onLoadModulesPostFromInit($e)
	{
		Storage::$order[] = '....------------ CheckOrder\Module::onLoadModulesPostFromInit() -------------------';
		Storage::$order[] = '....Param: ' . get_class($e);
		Storage::$order[] = '....Target: ' . get_class($e->getTarget());
		$this->customEvent(__FUNCTION__);
	}
	
	public function onDispatchFromInit($e)
	{
		Storage::$order[] = '....------------ CheckOrder\Module::onDispatchFromInit() -------------------';
		Storage::$order[] = '....Param: ' . get_class($e);
		Storage::$order[] = '....Target: ' . get_class($e->getTarget());
		$this->customEvent(__FUNCTION__);
	}

	public function onDispatchFromBootstrapWontWork($e)
	{
	    // doesn't matter won't work anyhow!!!
	}
	
	public function onLoadModulesPostFromBootstrap($e)
	{
		Storage::$order[] = '....------------ CheckOrder\Module::onLoadModulesPostFromBootstrap() -------------------';
		Storage::$order[] = '....Param: ' . get_class($e);
		Storage::$order[] = '....Target: ' . get_class($e->getTarget());
		$this->customEvent(__FUNCTION__);
	}
	
	public function onDispatchFromBootstrap($e)
	{
		Storage::$order[] = '....------------ CheckOrder\Module::onDispatchFromBootstrap() -------------------';
		Storage::$order[] = '....Param: ' . get_class($e);
		Storage::$order[] = '....Target: ' . get_class($e->getTarget());
		$this->customEvent(__FUNCTION__);
	}
	
	private function customEvent($target) 
	{
		$em = new EventManager('sharedInit');
		$em->trigger('customEventInit', $target);
		$em = new EventManager('customEventBootstrap');
		$em->trigger('customEventBootstrap', $target);
	}

	public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
    	return array(
    		'services' => array(
    			// comment out below and re-run http://zf2.unlikelysource.local/check
    			'check-test' => array('key' => 'FROM MODULE.PHP', 'Module.php:' . microtime()),
       		),
    	);
    }
}
