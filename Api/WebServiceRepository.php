<?php
namespace Bangerkuwranger\Couponcodeapi\Api;
use Bangerkuwranger\Couponcodeapi\Api\WebServiceRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnectionFactory;
use Magento\Customer\Model\CustomerFactory;
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
     * @var CustomerRepositoryInterface
     */
    protected $_custs; 
     /**
     * @var CustomerFactory
     */
    protected $_custFactory;
    /**
     * WebServiceRepository constructor.
     *
     * @param ResourceConnectionFactory $_resourceConnection
     */
    public function __construct( ResourceConnectionFactory $_resourceConnection, RuleRepositoryInterface $_rules, CustomerRepositoryInterface $_custs, CustomerFactory $_custFactory ) {
    
        $this->_resourceConnection = $_resourceConnection;
        $this->_rules = $_rules;
        $this->_custs = $_custs;
        $this->_custFactory = $_custFactory;
    
    }
    /**
     * @return mixed
     */
    public function getCartRule( $ruleId, $returnObj = false ) {
    
        $rule = $this->_rules->getById( $ruleId );
        if( $returnObj ) {
        
        	return $rule;
        
        }
        $ruleData = array(
        	"name"				=> $rule->getName(),
        	"description"		=> $rule->getDescription(),
        	"fromDate"			=> $rule->getFromDate(),
        	"toDate"			=> $rule->getToDate(),
        	"usesPerCust"		=> $rule->getUsesPerCustomer(),
        	"usesPerCoupon"		=> $rule->getUsesPerCoupon(),
        	"isActive"			=> ( $rule->getIsActive() ) ? true : false,
        	"conditions"		=> $rule->getConditionsSerialized(),
        	"actions"			=> $rule->getActionsSerialized(),
        	"customerGroups"	=> $rule->getCustomerGroupIds(),
        	"storeLabel"		=> $rule->getStoreLabel(),
        );
        return $ruleData;
   
    }
    /**
     * @return string
     */
    public function getCouponCode( $ruleId, $custId = null, $email = null, $fname = null, $lname = null, $qty = 1, $length = 10, $format = 'alphanum' ) {
		
		$rule = $this->getCartRule( $ruleId, true );
		if( null !== $custId ) {
		
			$cust = $this->_custs->getById( $custId );
		
		}
		elseif( null !== $email ) {
		
			$websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
			$cust = $this->_custFactory->create();
			$cust->setWebsiteId( $websiteId );
			$cust->setEmail( $email );
			if( null !== $fname ) {
			
				$cust->setFirstname( $fname );
			
			}
			if( null !== $lname ) {
			
				$cust->setLastname( $lname );
			
			}
			$cust->setPassword( $cust->generatePassword( 8 ) );
			try {
			
				$cust->save();
				$cust->sendNewAccountEmail();
			
			}
			catch( \Exception $e ) {
			
				if( $e instanceof \Magento\Framework\Exception\LocalizedException || $cust->getId() ) {
				
					throw $e;
				
				}
			
			}
		
		}
		$custGroup = $cust->getGroupId();
		$ruleGroups = $rule->getCustomerGroupIds();
		$custInRuleGroup = in_array( $custGroup, $ruleGroups, true );
		//logic from Magento\SalesRule\Model\Rule->aquireCoupon... but returns actual code if autogenerating.
		if( $coupon->getCouponType() == 1 ) {
		
            return null;
        
        }
        if( $this->getCouponType() == 2 ) {
        
            return $this->getPrimaryCoupon();
        
        }
        /** @var \Magento\SalesRule\Model\Coupon $coupon */
        $coupon = $this->_couponFactory->create();
        $coupon->setRule(
            $this
        )->setIsPrimary(
            false
        )->setUsageLimit(
            $this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null
        )->setUsagePerCustomer(
            $this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null
        )->setExpirationDate(
            $this->getToDate()
        );

        $couponCode = $rule->getCouponCodeGenerator()->generateCode();
        $coupon->setCode( $couponCode );

        $ok = false;
		if( $coupon->getId() ) {
		
			try {
			
				$coupon->save();
			
			}
			catch (\Exception $e) {
			
				if( $e instanceof \Magento\Framework\Exception\LocalizedException || $coupon->getId() ) {
				
					throw $e;
				
				}
				$couponCode = $couponCode . $rule->getCouponCodeGenerator()->getDelimiter() . sprintf( '%04u', rand( 0, 9999 ) );
				$coupon->setCode( $couponCode );
			
			}
			$ok = true;
		
		}
        if( !$ok ) {
        
            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t acquire coupon.'));
        
        }

        return $couponCode;
    
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
