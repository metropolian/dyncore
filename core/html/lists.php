<?php

    class DynMenu
    {
        public $ListElement = 'ul';        
        
        public $Attrs = array();
        public $ItemAttrs = array();
        
        public $LinkAttrs = array();
        
        public $Text = '';
        
        public $Items = array();
        
        function __construct($Type = 'list', $Attrs = array())
        {
            switch($Type) 
            {
                case 'list' :
                    $this->Attrs = array('class' => 'list-group');
                    $this->ItemAttrs = array('class' => 'list-group-item');
                    break;
                
                case 'tab':
                    $this->Attrs = array('class' => 'nav nav-tabs');
                    $this->ItemAttrs = array('class' => '');
                    $this->LinkAttrs = array('data-toggle' => 'tab');
                    break;
                
                case 'navtab':
                    $this->Attrs = array('class' => 'nav nav-pills nav-stacked');
                    $this->ItemAttrs = array('class' => '');
                    $this->LinkAttrs = array('data-toggle' => 'tab');
                    break;
                
                case 'nav':
                    $this->Attrs = array('class' => 'nav nav-pills nav-stacked');
                    $this->ItemAttrs = array('class' => '');
                    break;
                
                case 'navbar':
                    $this->Attrs = array('class' => 'nav navbar-nav');
                    $this->ItemAttrs = array('class' => '');
                    break;
                
                case 'navbar-right':
                    $this->Attrs = array('class' => 'nav navbar-nav navbar-right');
                    $this->ItemAttrs = array('class' => '');
                    break;
                
                case 'menu':
                    $this->Attrs = array('class' => 'nav nav-pills');
                    $this->ItemAttrs = array('class' => '');
                    break;
                
                case 'dropdown':
                    $this->Attrs = array('class' => 'dropdown-menu', 'role' => 'menu');
                    $this->ItemAttrs = array('class' => '');
                    break;
            }
        }
        
        function Add($V)
        {
            $this->Items[] = $V;
        }
        
        function AddControls($V)
        {
            if (is_array($V))
            foreach($V as $D)
            $this->Items[] = $D;
        }
        
        function AddSubmenu($Type = '')
        {            
            $V = new DynMenu('dropdown');
            $this->Items[] = $V;
            return $V;
        }
        
        
        function Render($Display = true)
        {            
            $res = Html_Element($this->ListElement, $this->Attrs);
            
            foreach($this->Items as $Item)
            {
                $ItemAttrs = $this->ItemAttrs;
                
                if (is_string($Item))
                    $res .= Html_Element('li', $ItemAttrs, $Item);
                else if (is_a($Item, 'DynMenu'))
                {                    
                    LogWrite('debug',$this->LinkAttrs);
                    $res .= Html_Element('li', $ItemAttrs);                    
                    $res .= Html_Element('a', array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#', 'role' => 'button'), $Item->Text);
                    $res .= $Item->Render(false);
                    $res .= "</li>\r\n";
                }                    
                else if (is_array($Item))
                {
                    if ($Item['active'])
                    {
                        $ItemAttrs['class'] = Html_Classes($ItemAttrs['class'], 'active');
                        unset($Item['active']);
                    }
                    
                    $res .= Html_Element('li', $ItemAttrs);
                    if ($Item['href'] != '')
                    {
                        $Text = $Item['text'];
                        if ($Text === null) $Text = '';
                        unset($Item['text']);
                        $res .= Html_Element('a', $Item, $Text);
                    }
                        
                    $res .= "</li>\r\n";
                }
            }
            
            $res .= "</{$this->ListElement}>\r\n";
            
            if ($Display)
                echo $res;
            return $res;
        }
        
    }

?>