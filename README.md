# Libero event bus using STOMP test

See https://github.com/libero/libero/issues/85 for details.

## Instructions

Run one of:
- `docker-compose -f docker-compose.activemq.yml up`
- `docker-compose -f docker-compose.rabbitmq.yml up`

Then send some messages by executing any of:

1. `docker-compose -f docker-compose.[variant].yml run send scholarly-articles.published`
2. `docker-compose -f docker-compose.[variant].yml run send scholarly-articles.updated`
3. `docker-compose -f docker-compose.[variant].yml run send blog-articles.published`
4. `docker-compose -f docker-compose.[variant].yml run send blog-articles.updated`

as many times at you like.

- Consumer 1 is implemented by two consumers which will compete for `blog-articles.*` and `scholarly-articles.*`.
- Consumer 2 only consumes `scholarly-articles.published`.

The consumers periodically unsubscribe temporarily, and fail to process messages.

## Observations

- ActiveMQ consumers have periodic duplicate messages, seems to be caused when subscribing to a queue with existing messages.
- RabbitMQ consumers have periodic 'Subscription not found' crashes.
