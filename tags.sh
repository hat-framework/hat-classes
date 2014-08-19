function _rmTag(){

	echo "---change url---"
	git remote set-url origin https://tigredonorte:12tm3flol@github.com/hat-framework/hat-classes.git
	
	echo "---Removendo Tag---"
	git tag -d $1	
	
	echo "---Enviando modificação Tag---"
	git push origin :refs/tags/$1
}

function _fnCommit(){
	echo "---Git ADD---"
	git add -A
	
	echo "---change url---"
	git remote set-url origin https://tigredonorte:12tm3flol@github.com/hat-framework/$1.git
	
	echo "---commit---"
	git commit -m 'Repositório inicial'
		
	echo "---Dando push---"
	git push --all
	
	echo "---Criando tag---"
	git tag -a v0.1.1 -m "Initial"
	
	echo "---Dando push das tags---"
	git push --tag
	
	cd ../
}
_fnCommit hat-classes