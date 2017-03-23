<?php

namespace Book\Controller;

use Application\Controller\BaseController;
use Book\Form\ContactAddForm;
use Book\Entity\Contact;

class IndexController extends BaseController
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
        
        return $this->redirect()->toRoute('/');
    }
    
    public function editAction()
    {
        $form = new CategoryAddForm;
        $status = $message = '';
        $em = $this->getEntityManager();
        
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $category = $em->find('Book\Entity\Contact', $id);
        if(empty($category)){
            $message = 'Категория не найдена';
            $status = 'error';
            $this->flashMessenger()->setNamespace($status)->addMessage($message);
            return $this->redirect()->toRoute('/');
        }
        
        $form->bind($category);
        
        $request = $this->getRequest();
        
        if($request->isPost())
        {
            $form->setData($request->getPost());
            if($form->isValid())
            {              
                $em->persist($category);
                $em->flush();
                
                $status = 'success';
                $message = 'Категория обновлена';
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
        
        return $this->redirect()->toRoute('/');
    }
    public function deleteAction()
    {      
        $em = $this->getEntityManager();      
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $status = 'success';
        $message = 'Запись удалена';
        
        try{
            $repository = $em->getRepository('Book\Entity\Contact');
            $category = $repository->find($id);
            $em->remove($category);
            $em->flush();
        }
        catch(\Exeption $ex){
            $status = 'error';
            $message = 'Ошибка удаления записи: ' . $ex->getMessage();
        }
        
        $this->flashMessenger()->setNamespace($status)->addMessage($message);
        
        return $this->redirect()->toRoute('/');
    }
}

