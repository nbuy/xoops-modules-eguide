<?php
# user/admin common use resources
# $Id: common.php,v 1.1 2010/04/04 06:27:21 nobu Exp $
// M�dulo traduzido para o Portugu�s do Brasil (portuguese) por
// Leco (m_ohse@hotmail.com) URL: http://xoopersBR.com

define( "_MD_ORDER_DATE", "Ordenar por data" );
define( "_MD_CSV_OUT", "Formato CSV" );
define( '_MD_EXTENT_DATE', 'Data aberta' );
define( '_MD_RESERV_PERSONS', 'Numero maximo de pessoas' );
define( "_MD_INFO_REQUEST", "Notificar usuarios registrados" );
define( "_MD_INFO_COUNT", "Contagem %d" );

global $expire_set, $edit_style, $ev_stats, $ev_extents, $rv_stats;

$expire_set = array(
	""        => "-- Texto usado --",
	"+0"      => "Mesmo dia",
	"+3600"   => "Uma hora",
	"+86400"  => "Proximo dia",
	"+172800" => "2 dias",
	"+259200" => "3 dias",
	"+604800" => "Uma semana"
);

$edit_style = array(
	0 => "Somente tags XOOPS",
	1 => "Fazer tag de nova linha &lt;br&gt;",
	2 => "Desabilitar tags HTML"
);

$ev_stats = array(
	0 => "Mostrar",
	1 => "Aguardando",
	4 => "Deletado"
);

$rv_stats = array(
	0 => "Aguardando",
	1 => "Agendando",
	2 => "Recusado"
);

$ev_extents = array(
	'none'    => 'Periodicidade',
	'daily'   => 'Diariamente',
	'weekly'  => 'Semanalmente',
	'monthly' => 'Mensalmente'
);
