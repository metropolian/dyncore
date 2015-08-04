<?php

    class DynPanelContainer
    { 
        public $Cont = 'div';
        public $ContAttrs = array( 'class' => '' );
    }

    class DynPanel
    {   
        public $Panel = 'div';
        public $PanelAttrs = array( 'class' => '' );

        public $PanelTitle = 'div';
        public $PanelTitleAttrs = array( 'class' => '' );

        public $PanelBody = 'div';
        public $PanelBodyAttrs = array( 'class' => '' );
        
        public $Title = '';
        
        function __construct($Type = '', $Id = '')
        {
            switch($Type)
            {
                case 'tab':
                    $this->PanelAttrs = array('class' => 'tab-pane panel panel-default');
                    $this->PanelTitleAttrs = array('class' => 'panel-heading');
                    $this->PanelBodyAttrs = array('class' => 'panel-body');
                break;
                
                default:
                    $this->PanelAttrs = array('class' => 'panel panel-default');
                    $this->PanelTitleAttrs = array('class' => 'panel-heading');
                    $this->PanelBodyAttrs = array('class' => 'panel-body');
                break;
            }
            $this->PanelAttrs['id'] = $Id;
        }
        
        
        function Begin()
        {
            $res .= Html_Element($this->Panel, $this->PanelAttrs);
            
            if ($this->Title != '')
                $res .= Html_Element($this->PanelTitle, $this->PanelTitleAttrs, $this->Title);            
            
            $res .= Html_Element($this->PanelBody, $this->PanelBodyAttrs);
            
            echo $res;
        }
        
        function End()
        {
            $res .= "</{$this->PanelBody}>";
            $res .= "</{$this->Panel}>";
            
            echo $res;
        }
        
    }

?>