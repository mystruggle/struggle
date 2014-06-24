#!/bin/bash

Flag=y
Version=3.0.3.0
Build=047000
Date=$(date +%Y%m%d)
Tag=Hotelv"$Version"_build"$Build"
Name=Hotelv"$Version"_build"$Build"_"$Date"
# �����������޸�Э���������
Protocol_bin=protocol_V2.0.0.30_build048000_32_20140428_hotel.bin

#==============pa����·��=====================
PAcodeURL=https://172.31.6.165/svn/rtx/branch/V3-6-0-0/PAcode/pa3.5
#Э���汾Ϊ2023������·�����Ժ���Э�������ר�ų�������Э��
SVNRULESPATH=https://172.31.6.165/svn/dc/pa_rules/V2-0-0-23
#Э�������·��
#SVNRULESPATH=https://172.31.6.165/svn/dc/pa_rules_v2/run_env
#===============MakePacket.log============
LOG=/tmp/MakePacket.log

#===============SVN��Ϣ==================
SVN_USER=zhuhongqiang
SVN_PASSWORD=zhq0402
SVN_USER_PA=liuqianrong
SVN_PASSWORD_PA=lql^!^

#===============Package=====================
PACKAGE_PATH=Package

#===============www�����·��==============
WWW_PATH=$PACKAGE_PATH/Hotelv3.0/apps/www
WWW_EXEC_PATH=$WWW_PATH/htdocs/hotel/exec

APPSLOG_PATH=$PACKAGE_PATH/Hotelv3.0/appslog
APPS_PATH=$PACKAGE_PATH/Hotelv3.0/apps


#===============apps/���·��====================
#hotel��·��
HOTEL_PATH=$PACKAGE_PATH/Hotelv3.0/apps/hotel


#===============run_env�����·��===============
#run_env��·��
RUN_ENV_PATH=$HOTEL_PATH/run_env

#run_env/lib��·��
RUN_ENV_LIB_PATH=$RUN_ENV_PATH/lib

#run_env/plcy.d��·��
RUN_ENV_PLCY_PATH=$RUN_ENV_PATH/plcy.d

#run_env/rc.d��·��
RUN_ENV_RC_PATH=$RUN_ENV_PATH/rc.d

#run_env/bin��·��
RUN_ENV_BIN_PATH=$RUN_ENV_PATH/bin

#===============forward�����·��==============
#forwardĿ¼
FORWARD_PATH=$HOTEL_PATH/forward

#forward/plcy.dĿ¼
FORWARD_PLCY_PATH=$FORWARD_PATH/plcy.d

#forward/binĿ¼
FORWARD_BIN_PATH=$FORWARD_PATH/bin

#===============TcpRealName�����·��==============
#realnameĿ¼
REALNAME_PATH=$HOTEL_PATH/realname

#===============tool�����·��================
#tool��Ŀ¼
TOOL_PATH=$HOTEL_PATH/tool

#===============CommClient����Ϣ=========
COMM_CLIENT_PATH=$HOTEL_PATH/comm_client
COMM_CLIENT_BIN_PATH=$COMM_CLIENT_PATH/bin
COMM_CLIENT_PLCY_PATH=$COMM_CLIENT_PATH/plcy.d
COMM_CLIENT_PLUGIN_PATH=$COMM_CLIENT_BIN_PATH/plugin

#================��������========================

#�Ƿ���ʽ����
function JudgePackage()
{
    while true
    do
        echo "�Ƿ���ʽ�������ǣ�������y����������n"
        read Flag
        if [[ $Flag == 'y' || $Flag == 'n' ]]
        then
            break
        fi
    done
}

#ȷ����ǰ��BUILD��
function GetBuildNumber()
{
    echo "������VERSION��,��ʽ������3.0.0.1:"
    read Version
    echo "������BUILD��,��ʽ������003000:"
    read Build
    Tag=Hotelv"$Version"_build"$Build"
    Name=Hotelv"$Version"_build"$Build"_"$Date"
}

#�������
function Compiling()
{
#��make���б���
	cpu_count=`cat /proc/cpuinfo  | grep processor | wc -l`
	echo "cpu����: $cpu_count"
	all_counts=`expr $cpu_count \* 2`
	make_counts="-j${all_counts}"
	echo "make���в���: ${make_counts}"
    rm -f "$LOG"

    echo "---------------------Make -f Makefile4lib--------------------">>$LOG
    make -f Makefile4lib clean >/dev/null 2>&1
    make -f Makefile4lib ${make_counts} >>$LOG 2>&1
    if [[ ! $? -eq 0 ]]
    then
        echo "make -f Makefile4lib fail, check '$LOG' for more information"
        exit 1
    fi

    make -f Makefile clean >/dev/null 2>&1
    make -f Makefile ${make_counts} >>"$LOG" 2>&1
	make -f Makefile4Inb clean >/dev/null 2>&1
    make -f Makefile4Inb ${make_counts} >>"$LOG" 2>&1

    if [[ ! $? -eq 0 ]]
    then
        echo "make -f Makefile fail, check '$LOG' for more information"
        exit 1
    fi 

	cd SendSig4JinGui/
	make
	cd ..
}


#���������Ķ������ļ�
function CopyBinary()
{
    Ret=0
    cp --reply=yes Forward/Forward              $FORWARD_BIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?
    
    Ret=0
    cp --reply=yes TcpRealName/TcpRealName      $REALNAME_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes UpdateClient/UpdateClient    $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes DiagnoseTool/DiaProfile      $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes DiagnoseTool/ReadAlm         $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes DiagnoseTool/ReadNet         $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/get_info                 $RUN_ENV_BIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/get_time_limit           $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/get_usbdog_info          $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/page_register            $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/RzxReg                   $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/getproduct               $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/isregister               $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes RunStat/RunStat               $TOOL_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes libPolicyManager/libpolicy_manager.so $RUN_ENV_LIB_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes Inbusinessclient/webservice/lib/libWebService.so $RUN_ENV_LIB_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes libQQPwd/libQQPwd.so     $RUN_ENV_LIB_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes UpdateProxy/UpdateProxy      $RUN_ENV_BIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes Inbusinessclient/Inbusinessclient  $COMM_CLIENT_BIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes UpLoad/libupload.so                $COMM_CLIENT_PLUGIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes Update/libupdate.so                $COMM_CLIENT_PLUGIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes SendStatus/libSendStatus.so        $COMM_CLIENT_PLUGIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    cp --reply=yes CGI/get_usbdog_info          $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes NetConfigure/Dhcp               $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes NetConfigure/DhcpSubnet         $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes NetConfigure/Nat                $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes NetConfigure/StaticIp           $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes NetConfigure/pppoe.exp          $WWW_EXEC_PATH/  1>>$LOG 2>&1 
    let Ret+=$?

    cp --reply=yes SendSig4JinGui/SendSig4JinGui	$RUN_ENV_BIN_PATH/ 1>>$LOG 2>&1
    let Ret+=$?

    if [[ ! $Ret -eq 0 ]]
    then
        echo "Copy Binary file failed, check '$LOG' for more information"
        exit 1
    fi

 	# debug  of  release
 	TYPE=debug
 #	MD5_NEW=`md5sum libQQPwd/QQCONTENT_3.5/release/qqcontent.so | awk '{print $1}'`
 	MD5_NEW=`md5sum libQQPwd/QQCONTENT_3.5/$TYPE/qqcontent.so | awk '{print $1}'`
	MD5_OLD=`md5sum Package/Hotelv3.0/apps/hotel/run_env/bin/plugin/qqcontent.so | awk '{print $1}'`
	if [[ ! $MD5_NEW = $MD5_OLD ]]
	then
		cp -f libQQPwd/QQCONTENT_3.5/$TYPE/qqcontent.so Package/Hotelv3.0/apps/hotel/run_env/bin/plugin/qqcontent.so
	fi

 	# two sh for initialization of hotel app system
 	if [[ ! -d Package/Hotelv3.0/apps/hotel_v3_autoscript ]]
 	then
 		mkdir Package/Hotelv3.0/apps/hotel_v3_autoscript
	fi
	cp -f hotel_v3_autoscript/*.sh Package/Hotelv3.0/apps/hotel_v3_autoscript/
}

#����tag
function MakeTag()
{
    svn rm -m $Tag https://172.31.6.165/svn/hotel/04.branch/$Tag
#svn mkdir -m $Tag https://172.31.6.165/svn/hotel/04.branch/$Tag
    svn cp -m $Tag --username $SVN_USER --password $SVN_PASSWORD https://172.31.6.165/svn/hotel/02.code/ https://172.31.6.165/svn/hotel/04.branch/$Tag
#  svn cp -m $Tag --username $SVN_USER --password $SVN_PASSWORD $PAcodeURL https://172.31.6.165/svn/hotel/04.branch/$Tag


}

#��ȡ���루����C��PHP��
function GetCode()
{
    tmppaurl=https://172.31.6.165/svn/rtx/branch/V3-6-0-0/PAcode

    if [[ $Flag == 'y' ]]
    then 
		echo "Pa3.5 will not compile.!"
        url=https://172.31.6.165/svn/hotel/04.branch/$Tag
#        PAcodeURL=${url}/PAcode/pa3.5
        svn co --username $SVN_USER --password $SVN_PASSWORD $url ./
#        svn export $tmppaurl --username $SVN_USER_PA --password $SVN_PASSWORD_PA
#        svn add PAcode
#        svn commit -m '��pa�Ĵ���Ҳ������ǩ' --username $SVN_USER --password $SVN_PASSWORD
        
    else
        url=https://172.31.6.165/svn/hotel/02.code/

        svn co --username $SVN_USER --password $SVN_PASSWORD $url ./
    fi


    cd ./C
}

#��ȡЭ�������Ĵ���
function CompilePAcode()
{

	cpu_count=`cat /proc/cpuinfo  | grep processor | wc -l`
	echo "cpu����: $cpu_count"
	all_counts=`expr $cpu_count \* 2`
	make_counts="-j${all_counts}"
	echo "make���в���: ${make_counts}"
if [[ ! -z $SVN ]]; then
    if [[ $Flag == 'y' ]]
    then
        PAcodeURL=${url}/PAcode/pa3.5
    	svn export --username $SVN_USER --password $SVN_PASSWORD $PAcodeURL
	else
		svn export --username $SVN_USER_PA --password $SVN_PASSWORD_PA $PAcodeURL
    fi
fi
	# add by zhq
	if [[ -d ../PAcode/pa3.5 ]]; then
		rm -rf pa3.5
		cp ../PAcode/pa3.5 ./ -a
	fi

    cp -rf libPolicyManager/libpolicy_manager.so pa3.5/lib
    cd pa3.5/src
    
    make -f Makefile4lib $make_counts >>$LOG || exit 1 ;
    make -f Makefile4lib install >>$LOG || exit 1;
    make -f Makefile4tool release hotel $make_counts >>$LOG || exit 1 ;
    make -f Makefile4tool install >>$LOG || exit 1;
    make release hotel $make_counts >>$LOG || exit 1;
    make install >>rtx35_build_${BUILD_DATE}.log || exit 1;
    make clean;make ipv6_plugin all $make_counts >>rtx35_build_${BUILD_DATE}.log || exit 1;
    make ipv6_plugin install >>rtx35_build_${BUILD_DATE}.log || exit 1;

    cp -af  ../run_env/bin/lib/*.so ../../$RUN_ENV_PATH/lib
    cp -af ../run_env/bin/plugin/*.so ../../$RUN_ENV_PATH/bin/plugin
    #cp -af ../run_env/rc.d/* ../../$RUN_ENV_PATH/rc.d
    # cp -af ../src/papde/rules/* ../../$RUN_ENV_PATH/bin/rules/
    cp -af ../run_env/bin/parse_post ../../$RUN_ENV_PATH/bin || exit 1

if [[ ! -z $SVN ]]; then
    echo "--------------------------�������µ�Э�������⵽��������Ŀ¼-------------------------------"
    rm -rf rules rc.d
	SVNRULESPATH=https://172.31.6.165/svn/dc/pa_rules_v2/run_env
	SVN_USER=liuqianrong
	SVN_PASSWORD=lql^!^
    svn export ${SVNRULESPATH}/bin/rules --username $SVN_USER --password $SVN_PASSWORD
    svn export ${SVNRULESPATH}/rc.d --username $SVN_USER --password $SVN_PASSWORD
    cp -af rules/* ../../$RUN_ENV_PATH/bin/rules/
    cp -arf rc.d/* ../../$RUN_ENV_PATH/rc.d/
fi

    cd ../../

 }

#����PHP���뵽Package
function GetWebpage()
{
    CUR_PATH=$(pwd)

    cd $WWW_PATH
	if [[ -e htdocs ]]
	then
		rm -rf htdocs
	fi
    mkdir htdocs
    cd htdocs

	#�ⲿ�ӿ�ʹ��, ע��������������ʱ��ҲҪ����������������,��Ϊsvn����û������ļ��е� 
	mkdir -p client
	echo "<?php" > login.htm
	echo "require('../hotel/client/authorize_portal.php');" >>login.htm

    mkdir hotel_new
    \cp -R ../../../../../../PHP/include/emaysms ./
    \cp -R ../../../../../../PHP/* ./hotel_new/
    \cp ./hotel_new/tmp/* ./
    /apps/bin/php doEncode.php >>/tmp/doencode.log
    \cp -R emaysms/include hotel/include/emaysms
    \cp -R emaysms/nusoaplib hotel/include/emaysms
    rm -rf hotel_new
    rm -rf emaysms
    

#����nobodyȨ��
    chown -R nobody:nobody .
    chmod 0777 hotel/_cache
    chmod 0777 hotel/log
    chmod 0777 hotel/upload
    chmod 0777 hotel/tmp
    chmod 0777 hotel/app/view_c
    chmod 0777 hotel/client

    cd $CUR_PATH
}

#�����ҳ
function ClearWebpage()
{
    rm -fr $WWW_PATH/htdocs
}

#���
function PackingPackage()
{
    CurPath=$(pwd)

    find Package -name .svn -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/apps -name readme.txt -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/appslog -name README.txt -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/appslog -name readme.txt -exec rm -fr {} \; >/dev/null 2>&1

    cd Package

    #��tar��
    tar -zcf $Name.tar.gz Hotelv3.0

    #��tar�����bin��
    Script=SetupBinary.Script
    TotalShLine=$(wc -l $Script | awk '{print $1}')

    let TotalShLine+=3
    echo "#/bin/bash" >$Name.bin
    echo "TotalShLine=$TotalShLine" >>$Name.bin
    cat $Script >>$Name.bin
    cat $Name.tar.gz >>$Name.bin
    chmod a+x $Name.bin

    #����һ������ϱ���������tar�����ϱ����е���������updateָ����·�����ű����ƣ�
    mkdir -p $Name
    mv Hotelv3.0 rtx_patch
    mv rtx_patch $Name/
    tar -zcf $Name.tar.gz $Name

    cd $CurPath
}

#��������Ϊ������Ŀ¼
function Patch_ChangeDir()
{
    #===============Package=====================
    PACKAGE_PATH=Patch

    #===============www�����·��==============
    WWW_PATH=$PACKAGE_PATH/Hotelv3.0/apps/www
    WWW_EXEC_PATH=$WWW_PATH/htdocs/hotel/exec

    APPSLOG_PATH=$PACKAGE_PATH/Hotelv3.0/appslog
    APPS_PATH=$PACKAGE_PATH/Hotelv3.0/apps

    #===============apps/���·��====================
    #hotel��·��
    HOTEL_PATH=Package/Hotelv3.0/apps/hotel

    #===============run_env�����·��===============
    #run_env��·��
    RUN_ENV_PATH=$HOTEL_PATH/run_env

    #run_env/lib��·��
    RUN_ENV_LIB_PATH=$RUN_ENV_PATH/lib

    #run_env/plcy.d��·��
    RUN_ENV_PLCY_PATH=$RUN_ENV_PATH/plcy.d

    #run_env/rc.d��·��
    RUN_ENV_RC_PATH=$RUN_ENV_PATH/rc.d

    #run_env/bin��·��
    RUN_ENV_BIN_PATH=$RUN_ENV_PATH/bin

    #===============forward�����·��==============
    #forwardĿ¼
    FORWARD_PATH=$HOTEL_PATH/forward

    #forward/plcy.dĿ¼
    FORWARD_PLCY_PATH=$FORWARD_PATH/plcy.d

    #forward/binĿ¼
    FORWARD_BIN_PATH=$FORWARD_PATH/bin

    #===============tool�����·��================
    #tool��Ŀ¼
    TOOL_PATH=$HOTEL_PATH/tool

    #===============CommClient����Ϣ=========
    COMM_CLIENT_PATH=$HOTEL_PATH/comm_client
    COMM_CLIENT_BIN_PATH=$COMM_CLIENT_PATH/bin
    COMM_CLIENT_PLCY_PATH=$COMM_CLIENT_PATH/plcy.d
    COMM_CLIENT_PLUGIN_PATH=$COMM_CLIENT_BIN_PATH/plugin
}

#������Ӧ�Ĳ���Ŀ¼
function Patch_MakeDir()
{
    mkdir -p $WWW_PATH
    mkdir -p $WWW_EXEC_PATH
    mkdir -p $APPSLOG_PATH
    mkdir -p $APPS_PATH
    mkdir -p $HOTEL_PATH
    mkdir -p $RUN_ENV_PATH
    mkdir -p $RUN_ENV_LIB_PATH
    #run_env/plcy.d��·��
    mkdir -p $RUN_ENV_PLCY_PATH
    #run_env/rc.d��·��
    mkdir -p $RUN_ENV_RC_PATH
    #run_env/bin��·��
    mkdir -p $RUN_ENV_BIN_PATH
    #forwardĿ¼
    mkdir -p $FORWARD_PATH
    #forward/plcy.dĿ¼
    mkdir -p $FORWARD_PLCY_PATH
    #forward/binĿ¼
    mkdir -p $FORWARD_BIN_PATH
    #realnameĿ¼
    mkdir -p $REALNAME_PATH
    #===============tool�����·��================
    #tool��Ŀ¼
    mkdir -p $TOOL_PATH
    #===============CommClient����Ϣ=========
    mkdir -p $COMM_CLIENT_PATH
    mkdir -p $COMM_CLIENT_BIN_PATH
    mkdir -p $COMM_CLIENT_PLCY_PATH
    mkdir -p $COMM_CLIENT_PLUGIN_PATH
}

#�򲹶���
function PackingPatch()
{
    CurPath=$(pwd)
    cd Patch

#�򲹶������ƶ���PackageĿ¼��
    tar -zcf Hotelv3.0_Patch_build"$Build"_"$Date".tar.gz Hotelv3.0
    mv Hotelv3.*.tar.gz ../Package/

    cd $CurPath

#ɾ��PatchĿ¼
    rm -fr Patch
}

#�����ײ�Ķ������ļ�
function  CopyRunEnv()
{
    if [[ -e Patch/Hotelv3.0/apps/hotel/run_env  ]]
    then 
        rm -fr Patch/Hotelv3.0/apps/hotel/run_env
    fi
    cp -R Package/Hotelv3.0/apps/hotel/run_env Patch/Hotelv3.0/apps/hotel/
    rm -fr Patch/Hotelv3.0/apps/hotel/run_env/rc.d/*
    rm -fr Patch/Hotelv3.0/apps/hotel/run_env/plcy.d/*

    if [[ -e Patch/Hotelv3.0/apps/www ]]
    then 
        rm -fr Patch/Hotelv3.0/apps/www
    fi
    cp -R Package/Hotelv3.0/apps/www Patch/Hotelv3.0/apps/ 
}

#���������������
function CleanCode()
{
    mv Package/$Name.tar.gz ../
    mv Package/$Name.bin ../
    cd ..
}

# Э������ 
function update_protocol()
{  # ��ǰĿ¼ΪC/ ��
	if [[ -f ../Protocol/$Protocol_bin ]]; then
		CurPath=$(pwd)
		cd ../Protocol/
		# ������ؼ��$Protocol_bin�ļ�����
		tail -n +29 $Protocol_bin > apps.tar.gz
		tar -zxf apps.tar.gz -C $CurPath/Package/Hotelv3.0/
		rm -rf apps.tar.gz

		cd $CurPath
	else
		echo "Not exec update_protocol"	
	fi
}
#==============�������================

rm -fr C
rm -fr PHP
rm -rf *.bin
rm -rf *.gz *.bz2
rm -rf .svn

JudgePackage
if [[ $Flag == 'y' ]]
then 
    GetBuildNumber
    MakeTag
fi
GetCode
GetWebpage
Compiling
#CompilePAcode
CopyBinary
#����Э���
update_protocol
PackingPackage

#��ʱ���Σ��Ժ�����Ƿ�򲹶���
#=============���ɲ�������============
#Patch_ChangeDir     #���������Ŀ¼
#Patch_MakeDir       #��ʱ��Ҫ������Ӧ�Ĳ���Ŀ¼
#CopyRunEnv          #���Ͽ�����Ҫ��run_env��������Ϊ���Ļ����ǲ���䶯��
#CopyBinary
#PackingPatch

#=============ɨβ�������������=======
CleanCode
