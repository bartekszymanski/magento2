<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Controller\Block;

class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Block
     */
    protected $action;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->layoutMock = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->disableOriginalConstructor()->getMock();

        $contextMock =
            $this->getMockBuilder('Magento\Framework\App\Action\Context')->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            'Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http'
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Framework\App\View')->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));

        $this->action = new \Magento\PageCache\Controller\Block\Render($contextMock);
    }

    public function testExecuteNotAjax()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(false));
        $this->requestMock->expects($this->once())->method('setActionName')->will($this->returnValue('noroute'));
        $this->requestMock->expects($this->once())->method('setDispatched')->will($this->returnValue(false));
        $this->action->execute();
    }

    /**
     * Test no params: blocks, handles
     */
    public function testExecuteNoParams()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->action->execute();
    }

    public function testExecute()
    {
        $blocks = ['block1', 'block2'];
        $handles = ['handle1', 'handle2'];
        $expectedData = ['block1' => 'data1', 'block2' => 'data2'];

        $blockInstance1 = $this->getMock(
            'Magento\PageCache\Block\Controller\StubBlock',
            ['toHtml'],
            [],
            '',
            false
        );
        $blockInstance1->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block1']));

        $blockInstance2 = $this->getMock(
            'Magento\PageCache\Block\Controller\StubBlock',
            ['toHtml'],
            [],
            '',
            false
        );
        $blockInstance2->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block2']));

        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));
        $this->requestMock->expects(
            $this->at(1)
        )->method(
                'getParam'
            )->with(
                $this->equalTo('blocks'),
                $this->equalTo('')
            )->will(
                $this->returnValue(json_encode($blocks))
            );
        $this->requestMock->expects(
            $this->at(2)
        )->method(
                'getParam'
            )->with(
                $this->equalTo('handles'),
                $this->equalTo('')
            )->will(
                $this->returnValue(json_encode($handles))
            );
        $this->viewMock->expects($this->once())->method('loadLayout')->with($this->equalTo($handles));
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));
        $this->layoutMock->expects(
            $this->at(0)
        )->method(
                'getBlock'
            )->with(
                $this->equalTo($blocks[0])
            )->will(
                $this->returnValue($blockInstance1)
            );
        $this->layoutMock->expects(
            $this->at(1)
        )->method(
                'getBlock'
            )->with(
                $this->equalTo($blocks[1])
            )->will(
                $this->returnValue($blockInstance2)
            );

        $this->responseMock->expects(
            $this->once()
        )->method(
                'appendBody'
            )->with(
                $this->equalTo(json_encode($expectedData))
            );

        $this->action->execute();
    }
}
