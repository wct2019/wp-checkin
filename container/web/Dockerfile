FROM nginx:alpine

ENV TZ Asia/Tokyo

COPY ./default.conf /etc/nginx/nginx.conf

CMD ["nginx", "-g", "daemon off;"]