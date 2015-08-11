<?php

    class DynForm
    {   
        public $FormAttrs = array();
        public $Inputs = array();
        
        public $Label = null;
        public $LabelAttrs = array('class' => 'control-label');

        public $InputAttrs = array('class' => 'form-control');
                
        public $InputGroup = 'div';
        public $InputGroupAttrs = array('class' => '');
                
        public $Group = 'div';
        public $GroupAttrs = array('class' => 'form-group');
        
        function __construct($Action, $Method = 'GET', $Type = 'horizontal', $Attrs = array())
        {
            switch($Type)
            {
                case 'inline':
                    $this->FormAttrs['class'] = 'form-inline';
                    $this->InputGroup = '';
                    break;
                
                case 'vertical':
                    $this->FormAttrs['class'] = '';
                    break;
                
                case 'horizontal':
                    $this->FormAttrs['class'] = 'form-horizontal';
                    $this->LabelAttrs = array('class' => 'control-label col-md-3');
                    $this->InputGroupAttrs = array('class' => 'input-group col-md-9');
                    break;
            }            
            $this->FormAttrs['action'] = $Action;
            $this->FormAttrs['method'] = $Method;            
            if (strtoupper($Method) == 'POST')
			     $this->FormAttrs['enctype'] = "multipart/form-data";
            foreach($Attrs as $K => $V)
                $this->FormAttrs[$K] = $V;
        }
        
        function Group($Name, $Attrs)
        {
            
        }
        
        function Label($Text)
        {
            $this->Label = $Text;
        }
        
        function Validation($Mode)
        {            
        }
        
        function AddControl($S)
        {
            if ($this->Label != '')
            {
                $A['label'] = $this->Label;
                $this->Label = '';
            }
            $A['type'] = $S['type'];
            $A['name'] = $S['name'];
            $A['id'] = $S['id']; 
            $A['value'] = $S['value'];
            $A['ops'] = $S['ops'];
            $A['label'] = $S['label'];            
            foreach($A as $K => $V)
                unset($S[$K]);
            $A['attrs'] = $this->InputAttrs;
            foreach($S as $K => $V)
                $A['attrs'][$K] = $S[$K];
            
            $this->Inputs[] = $A;
            return $R;
        }
        
        function AddInput($Type, $Name, $Value = '', $Ops = null, $Attrs = array())
        { 
            $A['attrs'] = $this->InputAttrs;
            $A['type'] = $Type;
            $A['name'] = $Name;
            $A['id'] = $Name;
            $A['value'] = $Value;
            $A['ops'] = $Ops;
            if (is_array($Attrs))
            foreach($Attrs as $K => $V)
                $A['attrs'][$K] = $V;
            
            return $this->AddControl($A);
        }
        
        function AddButton($Type, $Title, $Attrs = array())
        {
            $A['attrs'] = $Attrs;
            $A['type'] = $Type;
            $A['value'] = $Title;
            
            return $this->AddControl($A);
        }
        
        function AddTextarea($Name, $Value, $Cols = 60, $Rows = 5, $Attrs = array())
        {   
            $Attrs['cols'] = $Cols;
            $Attrs['rows'] = $Rows;
            
            return $this->AddInput('textarea', $Name, $Value, null, $Attrs);
        }
        
        function AddData($S, $V = null)
        {
            if (is_array($S))
            {
                foreach($S as $K => $V)
                    $this->AddData($K, $V);
                return;
            }
            
            $A['type'] = 'hidden';
            $A['name'] = $S;
            $A['value'] = $V;
            return $this->AddControl($A);
        }
        
        function AddControls($A)
        {
            foreach($A as $V)
                $this->AddControl($V);
        }
        
        
        
        
        
        function AddValues($V)
        {
            if (is_array($V))
                foreach($this->Inputs as &$A)
                {
                    $Name = $A['name'];
                    if ( isset( $V[$Name] ))
                        $A['value'] = $V[$Name];
                }
                    
        }
        
        function FindControl($Name)
        {   
            foreach($this->Inputs as $A)
                if ($A['name'] == $Name)
                    return $A;
            return null;
        }
        
        function RenderControl($A)
        {
            if ( is_string($A))
                $A = $this->FindControl($A);

            if (is_array($A))
            {   
                $res .= Html_Element($this->Group, $this->GroupAttrs);
                
                if (($A['type'] == 'hidden') || 
                    ($A['type'] == 'data')) 
                {                    
                    return Html_Input_Tag($A['type'], $A['name'], $A['value'], null);                    
                }
                
                
                $Label = $A['label'];
                if ($Label != '')
                {
                    $Attrs = $this->LabelAttrs;
                    $Attrs['for'] = $A['name'];
                    $res .= Html_Element('label', $Attrs, $Label);                            
                    $Label = '';
                }

                switch($A['type'])
                {
                case 'image':
                    if ($this->InputGroup != '')
                        $res .= Html_Element($this->InputGroup, $this->InputGroupAttrs);
                    
                    $res .= Html_Element('img', array('width' => $A['attrs']['width'], 'height' => $A['attrs']['height']), '');
                    $res .= Html_Input_Tag('hidden', $A['name'], $A['value'], $A['name'], '', array());
                    
                    if ($this->InputGroup != '')
                        $res .= "</{$this->InputGroup}>";
                    break;
                        
                case 'radio':                        
                    $res .= Html_Element($this->InputGroup, array('class' => 'radio'));
                    
                    if (is_array($A['ops']))
                    foreach($A['ops'] as $K => $V)
                    {
                        $res .= "<label>";
                        $res .= Html_Input_Tag($A['type'], $A['name'], $A['value'], $A['name'], $K, null);
                        //$res .= Html_Input_Tag($A['type'], $A['name'], $A['value'], $A['name'], $A['ops'], null);
                        $res .= " $V </label>\r\n";                    
                    }
                    $res .= "</{$this->InputGroup}>";
                    break;
                        
                case 'checkbox':                    
                    $res .= Html_Element($this->InputGroup, array('class' => 'checkbox'));                        
                    $res .= "<label>";
                    $res .= Html_Input_Tag($A['type'], $A['name'], $A['value'], $A['name'], $A['ops'], null);
                    $res .= "<div>{$A['attrs']['text']}</div>";
                    $res .= "</label>";                    
                    $res .= "</{$this->InputGroup}>";
                    break;
                        
                    
                default:
                    if ($this->InputGroup != '')
                        $res .= Html_Element($this->InputGroup, $this->InputGroupAttrs);
                    
                    $res .= Html_Input_Tag($A['type'], $A['name'], $A['value'], $A['name'], $A['ops'], $A['attrs']);
                    
                    if ($this->InputGroup != '')
                        $res .= "</{$this->InputGroup}>";
                }                
                $res .= "</{$this->Group}>\r\n";                
            }            
            return $res;
        }
        
        function Render($Name = null, $Display = true)
        {
            if ($Name === null)
            {
                $res = Html_Element('form', $this->FormAttrs);

                foreach($this->Inputs as $A)
                {
                    $res .= $this->RenderControl($A);
                }

                $res .= '</form>';
            }
            else
                $res = $this->RenderControl($Name);

            if ($Display)
                echo $res;
            return $res;                
        }
        
        
    }



?>