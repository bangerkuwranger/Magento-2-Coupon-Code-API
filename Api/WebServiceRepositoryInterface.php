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
     * @return mixed
     */
    public function getCartRule( $ruleId );
    /**
     * @param int $ruleId
     * @param int $custId
     * @param int $qty
     * @param int $length
     * @param string $format
     *
     * @return string
     */
    public function getCouponCode( $ruleId, $custId, $qty, $length, $format );
    /**
	 * @param string $email
	 *
     * @return int
     */
    public function getCustIdByEmail( $email );

}
