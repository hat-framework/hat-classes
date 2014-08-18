function _rmTag(){

	echo "---change url---"
	git remote set-url origin https://tigredonorte:12tm3flol@github.com/hat-framework/hat-classes.git
	
	echo "---Removendo Tag---"
	git tag -d $1	
	
	echo "---Enviando modificação Tag---"
	git push origin :refs/tags/$1
}
_rmTag v0.1.8
_rmTag v0.1.0