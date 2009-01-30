<?php
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301, USA

// Purpose:
// EC codes.

/*
typedef uint8_t ec_opcode_t;
typedef uint16_t ec_tagname_t;
typedef uint8_t ec_tagtype_t;
typedef uint32_t ec_taglen_t;
*/

// ProtocolVersion
define('EC_CURRENT_PROTOCOL_VERSION', 0x0200);

// ECFlags
define('EC_FLAG_ZLIB',         0x00000001);
define('EC_FLAG_UTF8_NUMBERS', 0x00000002);
define('EC_FLAG_HAS_ID',       0x00000004);
define('EC_FLAG_ACCEPTS',      0x00000010);
define('EC_FLAG_UNKNOWN_MASK', 0xff7f7f08);

// ECOpCodes
define('EC_OP_NOOP',                          0x01);
define('EC_OP_AUTH_REQ',                      0x02);
define('EC_OP_AUTH_FAIL',                     0x03);
define('EC_OP_AUTH_OK',                       0x04);
define('EC_OP_FAILED',                        0x05);
define('EC_OP_STRINGS',                       0x06);
define('EC_OP_MISC_DATA',                     0x07);
define('EC_OP_SHUTDOWN',                      0x08);
define('EC_OP_ADD_LINK',                      0x09);
define('EC_OP_STAT_REQ',                      0x0A);
define('EC_OP_GET_CONNSTATE',                 0x0B);
define('EC_OP_STATS',                         0x0C);
define('EC_OP_GET_DLOAD_QUEUE',               0x0D);
define('EC_OP_GET_ULOAD_QUEUE',               0x0E);
define('EC_OP_GET_WAIT_QUEUE',                0x0F);
define('EC_OP_GET_SHARED_FILES',              0x10);
define('EC_OP_SHARED_SET_PRIO',               0x11);
define('EC_OP_PARTFILE_REMOVE_NO_NEEDED',     0x12);
define('EC_OP_PARTFILE_REMOVE_FULL_QUEUE',    0x13);
define('EC_OP_PARTFILE_REMOVE_HIGH_QUEUE',    0x14);
define('EC_OP_PARTFILE_CLEANUP_SOURCES',      0x15);
define('EC_OP_PARTFILE_SWAP_A4AF_THIS',       0x16);
define('EC_OP_PARTFILE_SWAP_A4AF_THIS_AUTO',  0x17);
define('EC_OP_PARTFILE_SWAP_A4AF_OTHERS',     0x18);
define('EC_OP_PARTFILE_PAUSE',                0x19);
define('EC_OP_PARTFILE_RESUME',               0x1A);
define('EC_OP_PARTFILE_STOP',                 0x1B);
define('EC_OP_PARTFILE_PRIO_SET',             0x1C);
define('EC_OP_PARTFILE_DELETE',               0x1D);
define('EC_OP_PARTFILE_SET_CAT',              0x1E);
define('EC_OP_DLOAD_QUEUE',                   0x1F);
define('EC_OP_ULOAD_QUEUE',                   0x20);
define('EC_OP_WAIT_QUEUE',                    0x21);
define('EC_OP_SHARED_FILES',                  0x22);
define('EC_OP_SHAREDFILES_RELOAD',            0x23);
define('EC_OP_SHAREDFILES_ADD_DIRECTORY',     0x24);
define('EC_OP_RENAME_FILE',                   0x25);
define('EC_OP_SEARCH_START',                  0x26);
define('EC_OP_SEARCH_STOP',                   0x27);
define('EC_OP_SEARCH_RESULTS',                0x28);
define('EC_OP_SEARCH_PROGRESS',               0x29);
define('EC_OP_DOWNLOAD_SEARCH_RESULT',        0x2A);
define('EC_OP_IPFILTER_RELOAD',               0x2B);
define('EC_OP_GET_SERVER_LIST',               0x2C);
define('EC_OP_SERVER_LIST',                   0x2D);
define('EC_OP_SERVER_DISCONNECT',             0x2E);
define('EC_OP_SERVER_CONNECT',                0x2F);
define('EC_OP_SERVER_REMOVE',                 0x30);
define('EC_OP_SERVER_ADD',                    0x31);
define('EC_OP_SERVER_UPDATE_FROM_URL',        0x32);
define('EC_OP_ADDLOGLINE',                    0x33);
define('EC_OP_ADDDEBUGLOGLINE',               0x34);
define('EC_OP_GET_LOG',                       0x35);
define('EC_OP_GET_DEBUGLOG',                  0x36);
define('EC_OP_GET_SERVERINFO',                0x37);
define('EC_OP_LOG',                           0x38);
define('EC_OP_DEBUGLOG',                      0x39);
define('EC_OP_SERVERINFO',                    0x3A);
define('EC_OP_RESET_LOG',                     0x3B);
define('EC_OP_RESET_DEBUGLOG',                0x3C);
define('EC_OP_CLEAR_SERVERINFO',              0x3D);
define('EC_OP_GET_LAST_LOG_ENTRY',            0x3E);
define('EC_OP_GET_PREFERENCES',               0x3F);
define('EC_OP_SET_PREFERENCES',               0x40);
define('EC_OP_CREATE_CATEGORY',               0x41);
define('EC_OP_UPDATE_CATEGORY',               0x42);
define('EC_OP_DELETE_CATEGORY',               0x43);
define('EC_OP_GET_STATSGRAPHS',               0x44);
define('EC_OP_STATSGRAPHS',                   0x45);
define('EC_OP_GET_STATSTREE',                 0x46);
define('EC_OP_STATSTREE',                     0x47);
define('EC_OP_KAD_START',                     0x48);
define('EC_OP_KAD_STOP',                      0x49);
define('EC_OP_CONNECT',                       0x4A);
define('EC_OP_DISCONNECT',                    0x4B);
define('EC_OP_GET_DLOAD_QUEUE_DETAIL',        0x4C);
define('EC_OP_KAD_UPDATE_FROM_URL',           0x4D);
define('EC_OP_KAD_BOOTSTRAP_FROM_IP',         0x4E);

// ECTagNames
define('EC_TAG_STRING',                             0x0000);
define('EC_TAG_PASSWD_HASH',                        0x0001);
define('EC_TAG_PROTOCOL_VERSION',                   0x0002);
define('EC_TAG_VERSION_ID',                         0x0003);
define('EC_TAG_DETAIL_LEVEL',                       0x0004);
define('EC_TAG_CONNSTATE',                          0x0005);
define('EC_TAG_ED2K_ID',                            0x0006);
define('EC_TAG_LOG_TO_STATUS',                      0x0007);
define('EC_TAG_BOOTSTRAP_IP',                       0x0008);
define('EC_TAG_BOOTSTRAP_PORT',                     0x0009);
define('EC_TAG_CLIENT_ID',                          0x000A);
define('EC_TAG_CLIENT_NAME',                        0x0100);
define(    'EC_TAG_CLIENT_VERSION',                     0x0101);
define(    'EC_TAG_CLIENT_MOD',                         0x0102);
define('EC_TAG_STATS_UL_SPEED',                     0x0200);
define(    'EC_TAG_STATS_DL_SPEED',                     0x0201);
define(    'EC_TAG_STATS_UL_SPEED_LIMIT',               0x0202);
define(    'EC_TAG_STATS_DL_SPEED_LIMIT',               0x0203);
define(    'EC_TAG_STATS_UP_OVERHEAD',                  0x0204);
define(    'EC_TAG_STATS_DOWN_OVERHEAD',                0x0205);
define(    'EC_TAG_STATS_TOTAL_SRC_COUNT',              0x0206);
define(    'EC_TAG_STATS_BANNED_COUNT',                 0x0207);
define(    'EC_TAG_STATS_UL_QUEUE_LEN',                 0x0208);
define(    'EC_TAG_STATS_ED2K_USERS',                   0x0209);
define(    'EC_TAG_STATS_KAD_USERS',                    0x020A);
define(    'EC_TAG_STATS_ED2K_FILES',                   0x020B);
define(    'EC_TAG_STATS_KAD_FILES',                    0x020C);
define('EC_TAG_PARTFILE',                           0x0300);
define(    'EC_TAG_PARTFILE_NAME',                      0x0301);
define(    'EC_TAG_PARTFILE_PARTMETID',                 0x0302);
define(    'EC_TAG_PARTFILE_SIZE_FULL',                 0x0303);
define(    'EC_TAG_PARTFILE_SIZE_XFER',                 0x0304);
define(    'EC_TAG_PARTFILE_SIZE_XFER_UP',              0x0305);
define(    'EC_TAG_PARTFILE_SIZE_DONE',                 0x0306);
define(    'EC_TAG_PARTFILE_SPEED',                     0x0307);
define(    'EC_TAG_PARTFILE_STATUS',                    0x0308);
define(    'EC_TAG_PARTFILE_PRIO',                      0x0309);
define(    'EC_TAG_PARTFILE_SOURCE_COUNT',              0x030A);
define(    'EC_TAG_PARTFILE_SOURCE_COUNT_A4AF',         0x030B);
define(    'EC_TAG_PARTFILE_SOURCE_COUNT_NOT_CURRENT',  0x030C);
define(    'EC_TAG_PARTFILE_SOURCE_COUNT_XFER',         0x030D);
define(    'EC_TAG_PARTFILE_ED2K_LINK',                 0x030E);
define(    'EC_TAG_PARTFILE_CAT',                       0x030F);
define(    'EC_TAG_PARTFILE_LAST_RECV',                 0x0310);
define(    'EC_TAG_PARTFILE_LAST_SEEN_COMP',            0x0311);
define(    'EC_TAG_PARTFILE_PART_STATUS',               0x0312);
define(    'EC_TAG_PARTFILE_GAP_STATUS',                0x0313);
define(    'EC_TAG_PARTFILE_REQ_STATUS',                0x0314);
define(    'EC_TAG_PARTFILE_SOURCE_NAMES',              0x0315);
define(    'EC_TAG_PARTFILE_COMMENTS',                  0x0316);
define('EC_TAG_KNOWNFILE',                          0x0400);
define(    'EC_TAG_KNOWNFILE_XFERRED',                  0x0401);
define(    'EC_TAG_KNOWNFILE_XFERRED_ALL',              0x0402);
define(    'EC_TAG_KNOWNFILE_REQ_COUNT',                0x0403);
define(    'EC_TAG_KNOWNFILE_REQ_COUNT_ALL',            0x0404);
define(    'EC_TAG_KNOWNFILE_ACCEPT_COUNT',             0x0405);
define(    'EC_TAG_KNOWNFILE_ACCEPT_COUNT_ALL',         0x0406);
define(    'EC_TAG_KNOWNFILE_AICH_MASTERHASH',          0x0407);
define('EC_TAG_SERVER',                             0x0500);
define(    'EC_TAG_SERVER_NAME',                        0x0501);
define(    'EC_TAG_SERVER_DESC',                        0x0502);
define(    'EC_TAG_SERVER_ADDRESS',                     0x0503);
define(    'EC_TAG_SERVER_PING',                        0x0504);
define(    'EC_TAG_SERVER_USERS',                       0x0505);
define(    'EC_TAG_SERVER_USERS_MAX',                   0x0506);
define(    'EC_TAG_SERVER_FILES',                       0x0507);
define(    'EC_TAG_SERVER_PRIO',                        0x0508);
define(    'EC_TAG_SERVER_FAILED',                      0x0509);
define(    'EC_TAG_SERVER_STATIC',                      0x050A);
define(    'EC_TAG_SERVER_VERSION',                     0x050B);
define('EC_TAG_CLIENT',                             0x0600);
define(    'EC_TAG_CLIENT_SOFTWARE',                    0x0601);
define(    'EC_TAG_CLIENT_SCORE',                       0x0602);
define(    'EC_TAG_CLIENT_HASH',                        0x0603);
define(    'EC_TAG_CLIENT_FRIEND',                      0x0604);
define(    'EC_TAG_CLIENT_WAIT_TIME',                   0x0605);
define(    'EC_TAG_CLIENT_XFER_TIME',                   0x0606);
define(    'EC_TAG_CLIENT_QUEUE_TIME',                  0x0607);
define(    'EC_TAG_CLIENT_LAST_TIME',                   0x0608);
define(    'EC_TAG_CLIENT_UPLOAD_SESSION',              0x0609);
define(    'EC_TAG_CLIENT_UPLOAD_TOTAL',                0x060A);
define(    'EC_TAG_CLIENT_DOWNLOAD_TOTAL',              0x060B);
define(    'EC_TAG_CLIENT_STATE',                       0x060C);
define(    'EC_TAG_CLIENT_UP_SPEED',                    0x060D);
define(    'EC_TAG_CLIENT_DOWN_SPEED',                  0x060E);
define(    'EC_TAG_CLIENT_FROM',                        0x060F);
define(    'EC_TAG_CLIENT_USER_IP',                     0x0610);
define(    'EC_TAG_CLIENT_USER_PORT',                   0x0611);
define(    'EC_TAG_CLIENT_SERVER_IP',                   0x0612);
define(    'EC_TAG_CLIENT_SERVER_PORT',                 0x0613);
define(    'EC_TAG_CLIENT_SERVER_NAME',                 0x0614);
define(    'EC_TAG_CLIENT_SOFT_VER_STR',                0x0615);
define(    'EC_TAG_CLIENT_WAITING_POSITION',            0x0616);
define('EC_TAG_SEARCHFILE',                         0x0700);
define(    'EC_TAG_SEARCH_TYPE',                        0x0701);
define(    'EC_TAG_SEARCH_NAME',                        0x0702);
define(    'EC_TAG_SEARCH_MIN_SIZE',                    0x0703);
define(    'EC_TAG_SEARCH_MAX_SIZE',                    0x0704);
define(    'EC_TAG_SEARCH_FILE_TYPE',                   0x0705);
define(    'EC_TAG_SEARCH_EXTENSION',                   0x0706);
define(    'EC_TAG_SEARCH_AVAILABILITY',                0x0707);
define(    'EC_TAG_SEARCH_STATUS',                      0x0708);
define('EC_TAG_SELECT_PREFS',                       0x1000);
define(    'EC_TAG_PREFS_CATEGORIES',                   0x1100);
define(        'EC_TAG_CATEGORY',                           0x1101);
define(        'EC_TAG_CATEGORY_TITLE',                     0x1102);
define(        'EC_TAG_CATEGORY_PATH',                      0x1103);
define(        'EC_TAG_CATEGORY_COMMENT',                   0x1104);
define(        'EC_TAG_CATEGORY_COLOR',                     0x1105);
define(        'EC_TAG_CATEGORY_PRIO',                      0x1106);
define(    'EC_TAG_PREFS_GENERAL',                      0x1200);
define(        'EC_TAG_USER_NICK',                          0x1201);
define(        'EC_TAG_USER_HASH',                          0x1202);
define(        'EC_TAG_USER_HOST',                          0x1203);
define(    'EC_TAG_PREFS_CONNECTIONS',                  0x1300);
define(        'EC_TAG_CONN_DL_CAP',                        0x1301);
define(        'EC_TAG_CONN_UL_CAP',                        0x1302);
define(        'EC_TAG_CONN_MAX_DL',                        0x1303);
define(        'EC_TAG_CONN_MAX_UL',                        0x1304);
define(        'EC_TAG_CONN_SLOT_ALLOCATION',               0x1305);
define(        'EC_TAG_CONN_TCP_PORT',                      0x1306);
define(        'EC_TAG_CONN_UDP_PORT',                      0x1307);
define(        'EC_TAG_CONN_UDP_DISABLE',                   0x1308);
define(        'EC_TAG_CONN_MAX_FILE_SOURCES',              0x1309);
define(        'EC_TAG_CONN_MAX_CONN',                      0x130A);
define(        'EC_TAG_CONN_AUTOCONNECT',                   0x130B);
define(        'EC_TAG_CONN_RECONNECT',                     0x130C);
define(        'EC_TAG_NETWORK_ED2K',                       0x130D);
define(        'EC_TAG_NETWORK_KADEMLIA',                   0x130E);
define(    'EC_TAG_PREFS_MESSAGEFILTER',                0x1400);
define(        'EC_TAG_MSGFILTER_ENABLED',                  0x1401);
define(        'EC_TAG_MSGFILTER_ALL',                      0x1402);
define(        'EC_TAG_MSGFILTER_FRIENDS',                  0x1403);
define(        'EC_TAG_MSGFILTER_SECURE',                   0x1404);
define(        'EC_TAG_MSGFILTER_BY_KEYWORD',               0x1405);
define(        'EC_TAG_MSGFILTER_KEYWORDS',                 0x1406);
define(    'EC_TAG_PREFS_REMOTECTRL',                   0x1500);
define(        'EC_TAG_WEBSERVER_AUTORUN',                  0x1501);
define(        'EC_TAG_WEBSERVER_PORT',                     0x1502);
define(        'EC_TAG_WEBSERVER_GUEST',                    0x1503);
define(        'EC_TAG_WEBSERVER_USEGZIP',                  0x1504);
define(        'EC_TAG_WEBSERVER_REFRESH',                  0x1505);
define(        'EC_TAG_WEBSERVER_TEMPLATE',                 0x1506);
define(    'EC_TAG_PREFS_ONLINESIG',                    0x1600);
define(        'EC_TAG_ONLINESIG_ENABLED',                  0x1601);
define(    'EC_TAG_PREFS_SERVERS',                      0x1700);
define(        'EC_TAG_SERVERS_REMOVE_DEAD',                0x1701);
define(        'EC_TAG_SERVERS_DEAD_SERVER_RETRIES',        0x1702);
define(        'EC_TAG_SERVERS_AUTO_UPDATE',                0x1703);
define(        'EC_TAG_SERVERS_URL_LIST',                   0x1704);
define(        'EC_TAG_SERVERS_ADD_FROM_SERVER',            0x1705);
define(        'EC_TAG_SERVERS_ADD_FROM_CLIENT',            0x1706);
define(        'EC_TAG_SERVERS_USE_SCORE_SYSTEM',           0x1707);
define(        'EC_TAG_SERVERS_SMART_ID_CHECK',             0x1708);
define(        'EC_TAG_SERVERS_SAFE_SERVER_CONNECT',        0x1709);
define(        'EC_TAG_SERVERS_AUTOCONN_STATIC_ONLY',       0x170A);
define(        'EC_TAG_SERVERS_MANUAL_HIGH_PRIO',           0x170B);
define(        'EC_TAG_SERVERS_UPDATE_URL',                 0x170C);
define(    'EC_TAG_PREFS_FILES',                        0x1800);
define(        'EC_TAG_FILES_ICH_ENABLED',                  0x1801);
define(        'EC_TAG_FILES_AICH_TRUST',                   0x1802);
define(        'EC_TAG_FILES_NEW_PAUSED',                   0x1803);
define(        'EC_TAG_FILES_NEW_AUTO_DL_PRIO',             0x1804);
define(        'EC_TAG_FILES_PREVIEW_PRIO',                 0x1805);
define(        'EC_TAG_FILES_NEW_AUTO_UL_PRIO',             0x1806);
define(        'EC_TAG_FILES_UL_FULL_CHUNKS',               0x1807);
define(        'EC_TAG_FILES_START_NEXT_PAUSED',            0x1808);
define(        'EC_TAG_FILES_RESUME_SAME_CAT',              0x1809);
define(        'EC_TAG_FILES_SAVE_SOURCES',                 0x180A);
define(        'EC_TAG_FILES_EXTRACT_METADATA',             0x180B);
define(        'EC_TAG_FILES_ALLOC_FULL_SIZE',              0x180C);
define(        'EC_TAG_FILES_CHECK_FREE_SPACE',             0x180D);
define(        'EC_TAG_FILES_MIN_FREE_SPACE',               0x180E);
define(    'EC_TAG_PREFS_SRCDROP',                      0x1900);
define(        'EC_TAG_SRCDROP_NONEEDED',                   0x1901);
define(        'EC_TAG_SRCDROP_DROP_FQS',                   0x1902);
define(        'EC_TAG_SRCDROP_DROP_HQRS',                  0x1903);
define(        'EC_TAG_SRCDROP_HQRS_VALUE',                 0x1904);
define(        'EC_TAG_SRCDROP_AUTODROP_TIMER',             0x1905);
define(    'EC_TAG_PREFS_DIRECTORIES',                  0x1A00);
define(    'EC_TAG_PREFS_STATISTICS',                   0x1B00);
define(        'EC_TAG_STATSGRAPH_WIDTH',                   0x1B01);
define(        'EC_TAG_STATSGRAPH_SCALE',                   0x1B02);
define(        'EC_TAG_STATSGRAPH_LAST',                    0x1B03);
define(        'EC_TAG_STATSGRAPH_DATA',                    0x1B04);
define(        'EC_TAG_STATTREE_CAPPING',                   0x1B05);
define(        'EC_TAG_STATTREE_NODE',                      0x1B06);
define(        'EC_TAG_STAT_NODE_VALUE',                    0x1B07);
define(        'EC_TAG_STAT_VALUE_TYPE',                    0x1B08);
define(        'EC_TAG_STATTREE_NODEID',                    0x1B09);
define(    'EC_TAG_PREFS_SECURITY',                     0x1C00);
define(        'EC_TAG_SECURITY_CAN_SEE_SHARES',            0x1C01);
define(        'EC_TAG_IPFILTER_CLIENTS',                   0x1C02);
define(        'EC_TAG_IPFILTER_SERVERS',                   0x1C03);
define(        'EC_TAG_IPFILTER_AUTO_UPDATE',               0x1C04);
define(        'EC_TAG_IPFILTER_UPDATE_URL',                0x1C05);
define(        'EC_TAG_IPFILTER_LEVEL',                     0x1C06);
define(        'EC_TAG_IPFILTER_FILTER_LAN',                0x1C07);
define(        'EC_TAG_SECURITY_USE_SECIDENT',              0x1C08);
define(        'EC_TAG_SECURITY_OBFUSCATION_SUPPORTED',     0x1C09);
define(        'EC_TAG_SECURITY_OBFUSCATION_REQUESTED',     0x1C0A);
define(        'EC_TAG_SECURITY_OBFUSCATION_REQUIRED',      0x1C0B);
define(    'EC_TAG_PREFS_CORETWEAKS',                   0x1D00);
define(        'EC_TAG_CORETW_MAX_CONN_PER_FIVE',           0x1D01);
define(        'EC_TAG_CORETW_VERBOSE',                     0x1D02);
define(        'EC_TAG_CORETW_FILEBUFFER',                  0x1D03);
define(        'EC_TAG_CORETW_UL_QUEUE',                    0x1D04);
define(        'EC_TAG_CORETW_SRV_KEEPALIVE_TIMEOUT',       0x1D05);
define(    'EC_TAG_PREFS_KADEMLIA',                     0x1E00);
define(        'EC_TAG_KADEMLIA_UPDATE_URL',                0x1E01);

// EC_DETAIL_LEVEL
define('EC_DETAIL_CMD',           0x00);
define('EC_DETAIL_WEB',           0x01);
define('EC_DETAIL_FULL',          0x02);
define('EC_DETAIL_UPDATE',        0x03);
define('EC_DETAIL_INC_UPDATE',    0x04);

// EC_SEARCH_TYPE
define('EC_SEARCH_LOCAL',         0x00);
define('EC_SEARCH_GLOBAL',        0x01);
define('EC_SEARCH_KAD',           0x02);
define('EC_SEARCH_WEB',           0x03);

// EC_STATTREE_NODE_VALUE_TYPE
define('EC_VALUE_INTEGER',        0x00);
define('EC_VALUE_ISTRING',        0x01);
define('EC_VALUE_BYTES',          0x02);
define('EC_VALUE_ISHORT',         0x03);
define('EC_VALUE_TIME',           0x04);
define('EC_VALUE_SPEED',          0x05);
define('EC_VALUE_STRING',         0x06);
define('EC_VALUE_DOUBLE',         0x07);

// EcPrefs
define('EC_PREFS_CATEGORIES',     0x00000001);
define('EC_PREFS_GENERAL',        0x00000002);
define('EC_PREFS_CONNECTIONS',    0x00000004);
define('EC_PREFS_MESSAGEFILTER',  0x00000008);
define('EC_PREFS_REMOTECONTROLS', 0x00000010);
define('EC_PREFS_ONLINESIG',      0x00000020);
define('EC_PREFS_SERVERS',        0x00000040);
define('EC_PREFS_FILES',          0x00000080);
define('EC_PREFS_SRCDROP',        0x00000100);
define('EC_PREFS_DIRECTORIES',    0x00000200);
define('EC_PREFS_STATISTICS',     0x00000400);
define('EC_PREFS_SECURITY',       0x00000800);
define('EC_PREFS_CORETWEAKS',     0x00001000);
define('EC_PREFS_KADEMLIA',       0x00002000);
