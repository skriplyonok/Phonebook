<?php

namespace Book\Controller;

use Application\Controller\BaseController;
use Book\Form\ContactAddForm;
use Book\Form\ContactEditForm;
use Book\Entity\Contact;
use Book\Entity\Phone;
use Exception;

class ContactController extends BaseController
{
    public function indexAction()
    {

        $query = $this->getEntityManager()->createQuery('SELECT u FROM Book\Entity\Contact u ORDER BY u.id DESC');               
        $rows = $query->getResult();
        
        return array('contact' => $rows);
    }
    
    public function addAction()
    {
        $form = new ContactAddForm;
        $status = $message = '';
        $em = $this->getEntityManager();
        
        $request = $this->getRequest();
        if($request->isPost())
        {
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $contact = new Contact();
                $contact->exchangeArray($form->getData());
                                
                $em->persist($contact);
                $em->flush();
                
                $status = 'success';
                $message = 'Контакт добавлен';
            }else{
                $status = 'error';
                $message = 'Ошибка параметров';
            }
        }else{
            return array('form' => $form);
        }
        
        if($message){
            $this->flashMessenger()->setNamespace($status)->addMessage($message);
        }
        
        return $this->redirect()->toRoute('book/contact');
    }
    
    public function editAction()
    {

        $form = new ContactEditForm;

        $status = $message = '';
        $em = $this->getEntityManager();

        $id = (int) $this->params()->fromRoute('id', 0);

        $contact = $em->find('Book\Entity\Contact', $id);

        if(empty($contact)){
            $message = 'Контакт не найден';
            $status = 'error';
            $this->flashMessenger()->setNamespace($status)->addMessage($message);
            return $this->redirect()->toRoute('book/contact');
        }
        
        $form->bind($contact);
        
        $request = $this->getRequest();
              
        if($request->isPost())
        {
            $form->setData($request->getPost());
            if($form->isValid())
            {   
                try{
                    $em->persist($contact);
                    $em->flush();

                    //Вставка телефона
                    $query = $em->createQuery('SELECT COUNT(u.phone) c FROM Book\Entity\Phone u WHERE u.contact=' . $id);

                    $phonesInBase = $query->getResult()[0]['c'];

                    $phones = $request->getPost()['phone'];

                    $query = $em->createQuery('SELECT u FROM Book\Entity\Phone u ORDER BY u.id');
                    $rows = $query->getResult();
                    
                    foreach ($rows as $item){
                        if(in_array($item->getPhone(), $phones))
                        {
                            throw new Exception('Такой номер уже существует!');
                        }
                    }
                    
                    if($phonesInBase + count($phones) <= 10)
                    {
                        foreach ($phones as $val){
                            if(!empty($val)){
                                $phone = new Phone();
                                $phone->setContact($contact);
                                $phone->setPhone($val);
                                $em->persist($phone);
                                $em->flush();
                            }
                        }    
                        $status = 'success';
                        $message = 'Контакт обновлен';
                    }else{
                        $status = 'error';
                        $message = 'Нельзя добавить более 10 номеров!';                   
                }
                }catch(Exception $ex){
                    $status = 'error';
                    $message = 'Ошибка: ' . $ex->getMessage();                     
                }
               
            }else{
                $status = 'error';
                $message = 'Ошибка параметров';
                foreach ($form->getInputFilter()->getInvalidInput() as $errors){
                    foreach ($errors->getMessages() as $error){
                        $message .= ' ' . $error;
                    }
                }
            }
        }else{
            return array('form' => $form, 'id' => $id);
        }
        
        if($message){
            $this->flashMessenger()->setNamespace($status)->addMessage($message);
        }
        
        return $this->redirect()->toRoute('book/contact');
    }
    
    public function deleteAction()
    {      
        $em = $this->getEntityManager();      
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $status = 'success';
        $message = 'Запись удалена';
        
        try{
            $repository = $em->getRepository('Book\Entity\Contact');
            $contact = $repository->find($id);
            $em->remove($contact);
            $em->flush();
        }
        catch(\Exeption $ex){
            $status = 'error';
            $message = 'Ошибка удаления записи: ' . $ex->getMessage();
        }
        
        $this->flashMessenger()->setNamespace($status)->addMessage($message);
        
        return $this->redirect()->toRoute('book/contact');
    }
    
    public function viewAction()
    {
        $em = $this->getEntityManager();       
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $query = $em->createQuery('SELECT u FROM Book\Entity\Contact u WHERE u.id=' . $id);               
        $contact = $query->getResult();
        
        $query = $em->createQuery('SELECT u FROM Book\Entity\Phone u WHERE u.contact=' . $id);               
        $phones = $query->getResult(); 
        
        return array('contact' => $contact, 'phones' => $phones);     
    }
}

