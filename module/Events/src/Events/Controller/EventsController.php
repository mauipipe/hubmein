<?php

namespace Events\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel,
    Zend\Form\Form;

use Events\Service\EventService,
    Events\Service\RegionService,
    Events\Service\TagService;
    
use Events\DataFilter\EventFilter,
    Zend\View\Model\JsonModel;

use Zend\Mail\Transport;
use Zend\Mail\Message as Message;

class EventsController extends AbstractActionController
{
    
    /**
     * Event creation Form 
     *  
     * @var \Zend\Form\Form
     */
    private $promoteForm;
    
    /**
     * Main service for handling events (IE conferences)
     * 
     * @var \Events\Service\EventService
     */
    private $eventService;
    
    /**
     *
     * @var \Events\Service\RegionService 
     */
    private $regionService;
    
    /**
     * Class constructor
     * 
     * @param \Events\Service\EventService $eventService
     * @param \Zend\Form\Form $promoteForm
     */
    public function __construct( EventService $eventService,RegionService $regionService, Form $promoteForm ) {
       
        $this->regionService = $regionService;
        $this->eventService = $eventService;
        $this->promoteForm = $promoteForm;
        
    }
    
    /**
     * Returns a list of events, as fethched from model
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        
    	//$region = $this->getAndCheckNumericParam('region');
        $filter = $this->createFilterFromUrlParams($this->mergeRequest());
       
        // @todo pass class to event service
        $events = $this->eventService->getListByFilter($filter);
       
       
        return new ViewModel(array(
            
            'events' => $events,
            'regions'=>$this->regionService->getFullList(),
            
        ));
                
    }
    
    /**
     * Displays a specific event 
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function eventAction() {
     
    	$id = $this->getAndCheckNumericParam('id');
    	
        return new ViewModel(array(
                                 'event' => $this->eventService->getEvent($id)
                            )
        );
        
    }

    /**
     * Displays promote page (with form)
     * 
     * @return multitype:\Zend\Form\Form
     */
    public function promoteAction() {
        
        return array(
            'form' => $this->promoteForm,
        );
        
    }
    
    /**
     * Form post handling
     * 
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function processAction(){
    
        $form = $this->promoteForm;
    
        if ($this->request->isPost()) {
            $post = $this->request->getPost()->toArray();
            
            $form->setData($post);
            
            if(!$form->isValid()) {
                
                $model = new ViewModel(array(
                    'error' => true,
                    'form'  => $form,
                ));
                $model->setTemplate('events/events/promote');
                return $model;
            } 
            
            $this->eventService->insertEventFromArray($post);
            
            return $this->redirect()->toRoute('events/thanks');
          
        }
    }
    
    /**
     * Thank you action
     * 
     * After form has been submitted, user is sent here, so that 
     * a refresh user action won't harm model
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function thankyouAction() {
        
        return new ViewModel();
        
    }
    
    
    /**
     * Search events action
     * 
     * Retrieves events list according to the filters the user have defined 
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function searchAction() {
      
        $events = $this->searchEvents();
               
        $viewModel = new ViewModel(array('events' => $events));
        $viewModel->setTemplate('events/events/index');
               
        return $viewModel;
        
    }
    
    /**
     * Search events action
     * 
     * Retrieves events list according to the filters the user have defined 
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function countAction() {
      
        $eventsNr = $this->countEvents();
               
        $result = new JsonModel(array(
	    
            'count' => $eventsNr,
            'success'=>true,
            
        ));
        
        return $result;
        
    }
    
    
    /*
     * Private methods
     */
    private function listEvents() {
                         
        // get params from url and prepare EventFilter class
        $filter = $this->createFilterFromUrlParams($this->mergeRequest());
       
        // @todo pass class to event service
        return $this->eventService->countFilteredItems($filter);
        
    }
    
    
    private function countEvents() {
                         
        // get params from url and prepare EventFilter class
        $filter = $this->createFilterFromUrlParams($this->mergeRequest());
        // @todo pass class to event service
        return $this->eventService->countFilteredItems($filter);
        
    }

    
    /**
     * Merge region parameter from route with search request parameters
     * @return array $requestParams
     */
    private function mergeRequest() {
       
        $requestParams = $this->params()->fromQuery();
        $requestParamFromRoute = $this->params()->fromRoute();
        $requestParams['region'] = (isset($requestParamFromRoute['region']))?$requestParamFromRoute['region']:null; 
                
        return $requestParams;
        
    }
     
    
    /**
     * Build an EventFilter object from a given array
     * @param array $filteredRequest
     * @return \Events\DataFilter\EventFilter
     */
    private function createFilterFromUrlParams( array $filteredRequest ) {
               
        $eventFilter = EventFilter::createObjFromArray( $filteredRequest );
           
        return $eventFilter;
        
    }
    
    
}
