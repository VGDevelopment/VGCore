<?php

class UIScript {
    
    public function createUIs() {
        $ui = new SimpleForm('Menu', '');
        $button1 = new Button('Button 1');
        $button2 = new Button('Button 2 (with Image)');
        $button2->addImage(Button::IMAGE_TYPE_URL, 'https://pbs.twimg.com/profile_images/911270280127025157/uybzL-ys_400x400.jpg');
        $ui->addButton($button1);
        $ui->addButton($button2);
        self::$uis['exampleSimpleForm'] = UIDriver::addUI($this, $ui); // end of this form. start next form like you started this in same function on next line. Easy.
        // start. 
    }
    
}