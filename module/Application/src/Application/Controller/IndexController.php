<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Conferences\Service\ConferenceService;

use Conferences\DataFilter\RequestBuilder;

class IndexController extends AbstractActionController {
    
    private $conferenceService;
    
    public function __construct(ConferenceService $conferenceService) {
        
        $this->conferenceService = $conferenceService;
         
    }
    
    public function indexAction() {
             
        $requestBuilder = RequestBuilder::createObjFromArray(array());
        
              
        return new ViewModel(array(
                
                        'conferences' => $this->conferenceService->fetchAllByFilter($requestBuilder),
                
                    )
                );
        
    }
    
    public function aboutAction() {
        
        return new ViewModel();
        
    }
    
}
