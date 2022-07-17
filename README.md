# musinsa-api
## 무신사 PHP Back-end Engineer(주문서비스) 과제
- 과제 기간: 2022.07.11-2022.07.17 (7일간)
- 프로젝트 구성
	- PHP7.4
	- Codeigniter4.2.1
- API 목록

|  method | api path   | 기능  |
| ------------ | ------------ | ------------ |
|POST|/api/v1/dataInit|과제 테스트용 데이터 세팅 및 초기화 API|
|GET|/api/v1/orders|주문조회 API|
|GET|/api/v1/refund/expectation|반품비 예상 금액 조회 API|
|POST|/api/v1/refund/exchange|교환 접수 API|
|POST|/api/v1/refund/return|환불 접수 API|

- erd

![erd](https://user-images.githubusercontent.com/39252052/179399071-03d290eb-1b46-4cf8-8eca-6a2252ca9719.JPG)

## 프로젝트 실행방법 3가지
### 1. 윈도우10 > wsl2(ubuntu20.04.4 lts) + docker 사용

a. dockerfile 다운로드 or clone(https://github.com/kk99corn/musinsa-dockerfile)
```
#wsl 실행 후 clone
git clone https://github.com/kk99corn/musinsa-dockerfile
```

b. dockerfile 실행
```
#dockerfile 있는 위치로 이동
cd musinsa-dockerfile

#dockerfile 존재하는 디렉토리에서 docker build
docker build . -t musinsa

#docker build 완료 후 run
docker run -d --name musinsa-api -p 80:80 musinsa
```

c. 접속 확인
```
# swagger
http://localhost/swagger/index.html
# 주문조회 api
http://localhost/api/v1/orders?memberSeq=1&orderSeq=1
```
------------

### 2. 리눅스 > ubuntu20.04.4 lts

a. 패키지설치
```
apt-get update && apt-get -y install git vim apache2 php7.4 php7.4-common php7.4-mysql php7.4-mbstring php7.4-curl php7.4-xml php7.4-intl php7.4-sqlite3
```

b. 소스 clone
```
mkdir /home/web && cd /home/web
git clone https://github.com/kk99corn/musinsa-api.git
chmod 777 -R /home/web/musinsa-api/writable
```

c. virtualhost 설정
```
cd /etc/apache2/sites-available/ && cp 000-default.conf 000-default_old.conf
vim 000-default.conf
```

000-default.conf - 아래 내용 추가
```
<VirtualHost *:80>
        ServerName musinsa-api.com
        DocumentRoot /home/web/musinsa-api

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory /home/web/musinsa-api>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>
</VirtualHost>
```

d. rewrite 모듈 활성화 및 apache2 실행
```
a2enmod rewrite
service apache2 start
```

e. 접속 확인
```
# swagger
http://localhost/swagger/index.html
# 주문조회 api
http://localhost/api/v1/orders?memberSeq=1&orderSeq=1
```
------------
### 3. http://hahahoho5915.dothome.co.kr 사이트에서 기능 테스트

a. 접속 확인
```
# swagger
http://hahahoho5915.dothome.co.kr/swagger/index.html
# 주문조회 api
http://hahahoho5915.dothome.co.kr/api/v1/orders?memberSeq=1&orderSeq=1
```
