#!/bin/bash

Flag=y
Version=3.0.3.0
Build=047000
Date=$(date +%Y%m%d)
Tag=Hotelv"$Version"_build"$Build"
Name=Hotelv"$Version"_build"$Build"_"$Date"
# 制作整包需修改协议包的名字
Protocol_bin=protocol_V2.0.0.30_build048000_32_20140428_hotel.bin

#==============pa代码路径=====================
PAcodeURL=https://172.31.6.165/svn/rtx/branch/V3-6-0-0/PAcode/pa3.5
#协议库版本为2023的下载路径，以后由协议分析的专门出包升级协议
SVNRULESPATH=https://172.31.6.165/svn/dc/pa_rules/V2-0-0-23
#协议库最新路径
#SVNRULESPATH=https://172.31.6.165/svn/dc/pa_rules_v2/run_env
#===============MakePacket.log============
LOG=/tmp/MakePacket.log

#===============SVN信息==================
SVN_USER=zhuhongqiang
SVN_PASSWORD=zhq0402
SVN_USER_PA=liuqianrong
SVN_PASSWORD_PA=lql^!^

#===============Package=====================
PACKAGE_PATH=Package

#===============www的相关路径==============
WWW_PATH=$PACKAGE_PATH/Hotelv3.0/apps/www
WWW_EXEC_PATH=$WWW_PATH/htdocs/hotel/exec

APPSLOG_PATH=$PACKAGE_PATH/Hotelv3.0/appslog
APPS_PATH=$PACKAGE_PATH/Hotelv3.0/apps


#===============apps/相关路径====================
#hotel的路径
HOTEL_PATH=$PACKAGE_PATH/Hotelv3.0/apps/hotel


#===============run_env的相关路径===============
#run_env的路径
RUN_ENV_PATH=$HOTEL_PATH/run_env

#run_env/lib的路径
RUN_ENV_LIB_PATH=$RUN_ENV_PATH/lib

#run_env/plcy.d的路径
RUN_ENV_PLCY_PATH=$RUN_ENV_PATH/plcy.d

#run_env/rc.d的路径
RUN_ENV_RC_PATH=$RUN_ENV_PATH/rc.d

#run_env/bin的路径
RUN_ENV_BIN_PATH=$RUN_ENV_PATH/bin

#===============forward的相关路径==============
#forward目录
FORWARD_PATH=$HOTEL_PATH/forward

#forward/plcy.d目录
FORWARD_PLCY_PATH=$FORWARD_PATH/plcy.d

#forward/bin目录
FORWARD_BIN_PATH=$FORWARD_PATH/bin

#===============TcpRealName的相关路径==============
#realname目录
REALNAME_PATH=$HOTEL_PATH/realname

#===============tool的相关路径================
#tool的目录
TOOL_PATH=$HOTEL_PATH/tool

#===============CommClient的信息=========
COMM_CLIENT_PATH=$HOTEL_PATH/comm_client
COMM_CLIENT_BIN_PATH=$COMM_CLIENT_PATH/bin
COMM_CLIENT_PLCY_PATH=$COMM_CLIENT_PATH/plcy.d
COMM_CLIENT_PLUGIN_PATH=$COMM_CLIENT_BIN_PATH/plugin

#================函数定义========================

#是否正式出包
function JudgePackage()
{
    while true
    do
        echo "是否正式出包？是：请输入y，否：请输入n"
        read Flag
        if [[ $Flag == 'y' || $Flag == 'n' ]]
        then
            break
        fi
    done
}

#确定当前的BUILD号
function GetBuildNumber()
{
    echo "请输入VERSION号,格式类似于3.0.0.1:"
    read Version
    echo "请输入BUILD号,格式类似于003000:"
    read Build
    Tag=Hotelv"$Version"_build"$Build"
    Name=Hotelv"$Version"_build"$Build"_"$Date"
}

#编译代码
function Compiling()
{
#让make并行编译
	cpu_count=`cat /proc/cpuinfo  | grep processor | wc -l`
	echo "cpu个数: $cpu_count"
	all_counts=`expr $cpu_count \* 2`
	make_counts="-j${all_counts}"
	echo "make并行参数: ${make_counts}"
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


#拷贝编译后的二进制文件
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

#打上tag
function MakeTag()
{
    svn rm -m $Tag https://172.31.6.165/svn/hotel/04.branch/$Tag
#svn mkdir -m $Tag https://172.31.6.165/svn/hotel/04.branch/$Tag
    svn cp -m $Tag --username $SVN_USER --password $SVN_PASSWORD https://172.31.6.165/svn/hotel/02.code/ https://172.31.6.165/svn/hotel/04.branch/$Tag
#  svn cp -m $Tag --username $SVN_USER --password $SVN_PASSWORD $PAcodeURL https://172.31.6.165/svn/hotel/04.branch/$Tag


}

#获取代码（包括C和PHP）
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
#        svn commit -m '把pa的代码也做个标签' --username $SVN_USER --password $SVN_PASSWORD
        
    else
        url=https://172.31.6.165/svn/hotel/02.code/

        svn co --username $SVN_USER --password $SVN_PASSWORD $url ./
    fi


    cd ./C
}

#获取协议分析层的代码
function CompilePAcode()
{

	cpu_count=`cat /proc/cpuinfo  | grep processor | wc -l`
	echo "cpu个数: $cpu_count"
	all_counts=`expr $cpu_count \* 2`
	make_counts="-j${all_counts}"
	echo "make并行参数: ${make_counts}"
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
    echo "--------------------------拷贝最新的协议特征库到宾馆运行目录-------------------------------"
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

#拷贝PHP代码到Package
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

	#外部接口使用, 注意制作升级包的时候也要加上这三句命令行,因为svn上是没有这个文件夹的 
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
    

#更改nobody权限
    chown -R nobody:nobody .
    chmod 0777 hotel/_cache
    chmod 0777 hotel/log
    chmod 0777 hotel/upload
    chmod 0777 hotel/tmp
    chmod 0777 hotel/app/view_c
    chmod 0777 hotel/client

    cd $CUR_PATH
}

#清除网页
function ClearWebpage()
{
    rm -fr $WWW_PATH/htdocs
}

#打包
function PackingPackage()
{
    CurPath=$(pwd)

    find Package -name .svn -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/apps -name readme.txt -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/appslog -name README.txt -exec rm -fr {} \; >/dev/null 2>&1
    find Package/Hotelv3.0/appslog -name readme.txt -exec rm -fr {} \; >/dev/null 2>&1

    cd Package

    #打tar包
    tar -zcf $Name.tar.gz Hotelv3.0

    #将tar包打成bin包
    Script=SetupBinary.Script
    TotalShLine=$(wc -l $Script | awk '{print $1}')

    let TotalShLine+=3
    echo "#/bin/bash" >$Name.bin
    echo "TotalShLine=$TotalShLine" >>$Name.bin
    cat $Script >>$Name.bin
    cat $Name.tar.gz >>$Name.bin
    chmod a+x $Name.bin

    #增加一个针对老宾馆升级的tar包（老宾馆中的升级进程update指定了路径、脚本名称）
    mkdir -p $Name
    mv Hotelv3.0 rtx_patch
    mv rtx_patch $Name/
    tar -zcf $Name.tar.gz $Name

    cd $CurPath
}

#变更打包的为补丁的目录
function Patch_ChangeDir()
{
    #===============Package=====================
    PACKAGE_PATH=Patch

    #===============www的相关路径==============
    WWW_PATH=$PACKAGE_PATH/Hotelv3.0/apps/www
    WWW_EXEC_PATH=$WWW_PATH/htdocs/hotel/exec

    APPSLOG_PATH=$PACKAGE_PATH/Hotelv3.0/appslog
    APPS_PATH=$PACKAGE_PATH/Hotelv3.0/apps

    #===============apps/相关路径====================
    #hotel的路径
    HOTEL_PATH=Package/Hotelv3.0/apps/hotel

    #===============run_env的相关路径===============
    #run_env的路径
    RUN_ENV_PATH=$HOTEL_PATH/run_env

    #run_env/lib的路径
    RUN_ENV_LIB_PATH=$RUN_ENV_PATH/lib

    #run_env/plcy.d的路径
    RUN_ENV_PLCY_PATH=$RUN_ENV_PATH/plcy.d

    #run_env/rc.d的路径
    RUN_ENV_RC_PATH=$RUN_ENV_PATH/rc.d

    #run_env/bin的路径
    RUN_ENV_BIN_PATH=$RUN_ENV_PATH/bin

    #===============forward的相关路径==============
    #forward目录
    FORWARD_PATH=$HOTEL_PATH/forward

    #forward/plcy.d目录
    FORWARD_PLCY_PATH=$FORWARD_PATH/plcy.d

    #forward/bin目录
    FORWARD_BIN_PATH=$FORWARD_PATH/bin

    #===============tool的相关路径================
    #tool的目录
    TOOL_PATH=$HOTEL_PATH/tool

    #===============CommClient的信息=========
    COMM_CLIENT_PATH=$HOTEL_PATH/comm_client
    COMM_CLIENT_BIN_PATH=$COMM_CLIENT_PATH/bin
    COMM_CLIENT_PLCY_PATH=$COMM_CLIENT_PATH/plcy.d
    COMM_CLIENT_PLUGIN_PATH=$COMM_CLIENT_BIN_PATH/plugin
}

#创建对应的补丁目录
function Patch_MakeDir()
{
    mkdir -p $WWW_PATH
    mkdir -p $WWW_EXEC_PATH
    mkdir -p $APPSLOG_PATH
    mkdir -p $APPS_PATH
    mkdir -p $HOTEL_PATH
    mkdir -p $RUN_ENV_PATH
    mkdir -p $RUN_ENV_LIB_PATH
    #run_env/plcy.d的路径
    mkdir -p $RUN_ENV_PLCY_PATH
    #run_env/rc.d的路径
    mkdir -p $RUN_ENV_RC_PATH
    #run_env/bin的路径
    mkdir -p $RUN_ENV_BIN_PATH
    #forward目录
    mkdir -p $FORWARD_PATH
    #forward/plcy.d目录
    mkdir -p $FORWARD_PLCY_PATH
    #forward/bin目录
    mkdir -p $FORWARD_BIN_PATH
    #realname目录
    mkdir -p $REALNAME_PATH
    #===============tool的相关路径================
    #tool的目录
    mkdir -p $TOOL_PATH
    #===============CommClient的信息=========
    mkdir -p $COMM_CLIENT_PATH
    mkdir -p $COMM_CLIENT_BIN_PATH
    mkdir -p $COMM_CLIENT_PLCY_PATH
    mkdir -p $COMM_CLIENT_PLUGIN_PATH
}

#打补丁包
function PackingPatch()
{
    CurPath=$(pwd)
    cd Patch

#打补丁包并移动到Package目录下
    tar -zcf Hotelv3.0_Patch_build"$Build"_"$Date".tar.gz Hotelv3.0
    mv Hotelv3.*.tar.gz ../Package/

    cd $CurPath

#删除Patch目录
    rm -fr Patch
}

#拷贝底层的二进抽文件
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

#拷贝包，清除代码
function CleanCode()
{
    mv Package/$Name.tar.gz ../
    mv Package/$Name.bin ../
    cd ..
}

# 协议升级 
function update_protocol()
{  # 当前目录为C/ 下
	if [[ -f ../Protocol/$Protocol_bin ]]; then
		CurPath=$(pwd)
		cd ../Protocol/
		# 这里务必检查$Protocol_bin文件内容
		tail -n +29 $Protocol_bin > apps.tar.gz
		tar -zxf apps.tar.gz -C $CurPath/Package/Hotelv3.0/
		rm -rf apps.tar.gz

		cd $CurPath
	else
		echo "Not exec update_protocol"	
	fi
}
#==============打包过程================

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
#更新协议包
update_protocol
PackingPackage

#暂时屏蔽，以后决定是否打补丁包
#=============生成补丁过程============
#Patch_ChangeDir     #变更补丁的目录
#Patch_MakeDir       #此时需要创建对应的补丁目录
#CopyRunEnv          #马上拷贝需要的run_env环境，因为这块的环境是不会变动的
#CopyBinary
#PackingPatch

#=============扫尾工作，清除代码=======
CleanCode
