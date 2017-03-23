<?php
namespace Bangerkuwranger\Couponcodeapi\Api;
use Bangerkuwranger\Couponcodeapi\Api\WebServiceRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Coupon\CodegeneratorFactory;
/**
 * Class WebServiceRepository
 * @package Bangerkuwranger\Couponcodeapi\Api
 */
class WebServiceRepository implements WebServiceRepositoryInterface {

    /**
     * @var ResourceConnectionFactory
     */
    protected $_resourceConnection;
    /**
     * @var RuleRepositoryInterface
     */
    protected $_rules;
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_custs; 
     /**
     * @var CustomerFactory
     */
    protected $_custFactory;
    /**
     * @var CouponFactory
     */
    protected $_couponFactory;
     /**
     * @var CodegeneratorFactory
     */
    protected $_codegenFactory;
    /**
     * WebServiceRepository constructor.
     *
     * @param ResourceConnectionFactory $_resourceConnection
     */
    public function __construct( ResourceConnectionFactory $_resourceConnection, RuleRepositoryInterface $_rules, RuleFactory $_ruleFactory, CustomerRepositoryInterface $_custs, CustomerFactory $_custFactory, CouponFactory $_couponFactory, CodegeneratorFactory $_codegenFactory ) {
    
        $this->_resourceConnection = $_resourceConnection;
        $this->_rules = $_rules;
        $this->_ruleFactory = $_ruleFactory;
        $this->_custs = $_custs;
        $this->_custFactory = $_custFactory;
        $this->_couponFactory = $_couponFactory;
        $this->_codegenFactory = $_codegenFactory;
    
    }
    /**
     * @return \stdClass
     */
    public function getCartRule( $ruleId, $returnObj = false ) {
    
        $rule = $this->_rules->getById( $ruleId );
        if( $returnObj ) {
        
        	return $rule;
        
        }
        $ruleData =  array(
        	"name"				=> $rule->getName(),							//[0](string)
        	"description"		=> $rule->getDescription(),						//[1](string)
        	"fromDate"			=> $rule->getFromDate(),						//[2](string){YYYY-MM-DD}
        	"toDate"			=> $rule->getToDate(),							//[3](string){YYYY-MM-DD}
        	"usesPerCust"		=> intval( $rule->getUsesPerCustomer() ),		//[4](int)
        	"usesPerCoupon"		=> intval( $rule->getUsesPerCoupon() ),			//[5](int)
        	"isActive"			=> ( $rule->getIsActive() ) ? true : false,		//[6](bool)
        	"customerGroups"	=> $rule->getCustomerGroupIds(),				//[7](array)[string-groupid]
        );
        
// 		$ruleData = new \stdClass();
// 		$ruleData->name = $rule->getName();
// 		$ruleData->description = $rule->getDescription();
// 		$ruleData->fromDate = $rule->getFromDate();
// 		$ruleData->toDate = $rule->getToDate();
// 		$ruleData->usesPerCust = intval( $rule->getUsesPerCustomer() );
// 		$ruleData->usesPerCoupon = intval( $rule->getUsesPerCoupon() );
// 		$ruleData->isActive = ( $rule->getIsActive() ) ? true : false;
// 		$ruleData->customerGroups = $rule->getCustomerGroupIds();
//someday.... actually, probably want to make another interface for this, and implement setters/getters for SOAPy folk
		
        return $ruleData;
   
    }
    /**
     * @return string
     */
    public function getCouponCode( $ruleId, $custId = null ) {
		
		$rule = $this->_ruleFactory->create()->load( $ruleId );
		$ruleGroups = $rule->getCustomerGroupIds();
		$custGroup = array();
		$custNotRequired = ( null === $custId || 0 === $custId );
		if( null !== $custId && $custId ) {
		
			$cust = $this->_custs->getById( $custId );
			$custGroup = $cust->getGroupId();
		
		}
		if( $custNotRequired || in_array( $custGroup, $ruleGroups, true ) ) {
		
			$rule->setCouponType( 3 );
			$rule->save();
			$newCoupon = $rule->acquireCoupon( true, 1 );
			$newCoupon->setType( 1 );
			$newCoupon->setCreatedAt( date( 'Y-m-d h:i:s' ) );
			$newCoupon->setExpirationDate( null );
			$newCoupon->setTimesUsed( 0 );
			$newCoupon->save();
			$rule->setCouponType( 2 );
			$rule->setUseAutoGeneration( 1 );
			$rule->save();
			return $newCoupon->getCode();
		
		}
		else {
		
			throw new \Magento\Framework\Exception\LocalizedException(__('Customer is not in group for this discount'));
		
		}
    
    }
    
    public function getCustIdByEmail( $email ) {
    
    	$id = null;
    	try {
    	
    		$cust = $this->_custs->get( $email );
    		$id = $cust->getId();
    	
    	}
    	catch( \Exception $e ) {
    	
    		if( $e instanceof \Magento\Framework\Exception\NoSuchEntityException ) {
    		
    			return $e;
    		
    		}
    		else {
    		
    			throw $e;
    		
    		}
    	
    	}
    	
    	return $id;
    
    }

}
