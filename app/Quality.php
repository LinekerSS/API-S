<?php

class Quality extends Core
{
    function __construct()
    {
        parent::__construct();
    }

    public function getMessage($params) {
        switch($params) {
            case 'Aguardando agendamento':
                echo 'Aguardando agendamento';
                break;
            case 'Em prcesso de devolução':
                echo 'Em prcesso de devolução'
                break;
            case 'Aguardando reagendamento':
                echo 'Aguardando reagendamento';
                break;
            case 'Em transferencia':
                echo 'Em transferencia';
                break;
            case 'Entrega cancelada':
                echo 'Entrega cancelada';
                break;
            case 'Nada coletado':
                echo 'Nada coletado';
                break;
            case 'Não entregue':
                echo 'Não entregue';
                break;
            case 'Coleta reversa':
                echo 'Coleta reversa';
                break;
            case 'Devolução':
                echo 'Devolução';
                break;
            case 'Em qualificação':
                echo 'Em qualificação';
                break;
            case 'Em transferencia':
                echo 'Em transferencia';
                break;
            case 'Em rota de entrega':
                echo 'Em rota de entrega';
                break;
            case 'Entregue':
                echo 'Entregue';
                break;
            case 'Não entregue':
                echo 'Não entregue';
                break;
            case 'Entregue':
                echo 'Entregue';
                break;
            case 'Em rota de entrega': 
                echo 'Em rota de entrega';
                break;
            case 'Aguardando para sair para entrega':
                echo 'Aguardando para sair para entrega';
                break;
            case 'Em rota de entrega':
                echo 'Em rota de entrega';
                break;
            case 'Reagendamento':
                echo 'Reagendamento';
                break;
            case 'Sinistro': 
                echo 'Sinistro'
                break;
            case 'Chegada a base de transferencia':
                echo 'Chegada a base de transferencia';
                break;
            default :
                echo " ERRO 404 - Não encontrado!";
                break;
        }
        return $params;        
    } 


   
}

