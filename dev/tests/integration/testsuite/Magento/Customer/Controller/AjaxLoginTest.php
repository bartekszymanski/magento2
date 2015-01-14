<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Controller;

use Magento\TestFramework\Helper\Bootstrap;

class AjaxLoginTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session');
        $session->loginById($customerId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogoutAction()
    {
        $this->login(1);
        $this->dispatch('customer/ajax/logout');
        $body = $this->getResponse()->getBody();
        $logoutMessage = Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data')->jsonDecode($body);
        $this->assertContains('Logout Successful', $logoutMessage['message']);
    }
}

