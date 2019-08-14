<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu {
    
	/**
     * Retrieve Adminhtml Menu array
     *
     * @return array
     */
    public function getMenuArray()
    {
		$notificationCount = Mage::helper('mobiadmin')->getCountUnreadNotification();
		$menuArray = $this->_buildMenuArray();
		$mobiadmin = $menuArray['mobiadmin'];
		if(is_array($mobiadmin)) {      
			if($notificationCount >= 1) {
				$menuArray['mobiadmin']['mobiclass'] = '<sup class="mobinotify">'.$notificationCount.'</sup>';
				$menuArray['mobiadmin']['children']['appnotification']['mobiclass'] = '<sup class="mobinotify">'.$notificationCount.'</sup>';
			}
            $menuArray['mobiadmin']['children']['appsupport']['click'] = 'window.open(\'http://support.mobi-commerce.net/\');';
		}
        return $menuArray;
    }

	public function getMenuLevel($menu, $level = 0)
    {
        $html = '<ul ' . (!$level ? 'id="nav"' : '') . '>' . PHP_EOL;
        foreach ($menu as $item) {
            $html .= '<li ' . (!empty($item['children']) ? 'onmouseover="Element.addClassName(this,\'over\')" '
                . 'onmouseout="Element.removeClassName(this,\'over\')"' : '') . ' class="' 
                . (!$level && !empty($item['active']) ? ' active' : '') . ' '
                
                . (!empty($item['children']) ? ' parent' : '')
                . (!empty($level) && !empty($item['last']) ? ' last' : '')
                . ' level' . $level . '"> <a href="' . $item['url'] . '" '
                . (!empty($item['title']) ? 'title="' . $item['title'] . '"' : '') . ' '
                . (!empty($item['click']) ? 'onclick="' . $item['click'] . '"' : '') . ' class="'
                . ($level === 0 && !empty($item['active']) ? 'active' : '') .' "><span>'
                . $this->escapeHtml($item['label']).''. (isset($item['mobiclass']) ? $item['mobiclass'] : ''). '</span></a>' . PHP_EOL;

            if (!empty($item['children'])) {
                $html .= $this->getMenuLevel($item['children'], $level + 1);
            }
            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul>' . PHP_EOL;
        return $html;
    }
}