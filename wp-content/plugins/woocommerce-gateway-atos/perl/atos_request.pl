#!/usr/bin/perl

print "Content-Type: text/html\n\n";

$donnees = $ENV{"QUERY_STRING"};

@tab = split("&", $donnees);
%data = ();

$parm = "";
foreach $i (@tab){
    ($nom, $valeur) = split("=", $i);
    $nom =~ s/%(..)/pack("c", hex($1))/ge;
    $valeur =~ s/%(..)/pack("c", hex($1))/ge;
    $valeur =~ s/\+/ /g;
    $data{"$nom"} = $valeur;
	
	if ($nom eq "bindir") {
		next;
	}

	$parm = $parm." '".$nom."=".$valeur."'";
}

$args = $data{'bindir'}.$parm;
system($args);

exit;
