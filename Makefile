PEAR=pear
PHPUNIT=phpunit
XSLTPROC=xsltproc
CP=cp
MKDIR=mkdir
RM=rm
VERSION=`./extract-version.sh`
CURVER=plor-$(VERSION).tgz
APIKEY=a011bbaf-336a-98c4-1547-0ee99ef7c990

all : 
	@echo "try :"
	@echo "make release "
	@echo "make push"


push:
	git push
	git push --tags

release: tagging pearing

tagging: $(CURVER)
	git tag -a -m "Version $(VERSION)"  v$(VERSION)

pearing: $(CURVER)
	@read -p "Who are you ? " toto && cat $(CURVER) | curl -u `echo $$toto`:$(APIKEY) -X POST --data-binary @- http://pear.pxxo.net/respear/

$(CURVER): package.xml
	$(PEAR) package $?

