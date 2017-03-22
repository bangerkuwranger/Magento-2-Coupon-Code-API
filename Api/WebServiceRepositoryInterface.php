<?php
namespace Bangerkuwranger\Couponcodeapi\Api;
/**
 * Interface WebServiceRepositoryInterface
 * @package Bangerkuwranger\Couponcodeapi\Api
 */
interface WebServiceRepositoryInterface {

    /**
	 * @param int $ruleId
	 *
     * @return \stdClass
     */
    public function getCartRule( $ruleId );
    /**
     * @param int $ruleId     
     * @param int $custId
     *
     * @return string
     */
    public function getCouponCode( $ruleId, $custId );
    /**
	 * @param string $email
	 *
     * @return int
     */
    public function getCustIdByEmail( $email );

}
