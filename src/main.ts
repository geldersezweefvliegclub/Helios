import { HttpAdapterHost, NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { INestApplication, Logger, ValidationPipe } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';
import { TransformInterceptor } from './core/interceptors/class-transform.interceptor';
import { HttpExceptionLogger } from './core/filters/http-exception-logger-filter/http-exception-logger.filter';
import { WinstonModule } from 'nest-winston';
import { SeqTransport } from '@datalust/winston-seq';
import * as winston from 'winston';

function setupSwagger(app: INestApplication, swaggerUrl: string) {
  const swaggerConfig = new DocumentBuilder()
    .setTitle('Helios API')
    .setDescription('De Helios API')
    .setVersion('1.0')
    .addBearerAuth()
    .build();

  const document = SwaggerModule.createDocument(app, swaggerConfig);
  SwaggerModule.setup(swaggerUrl, app, document);
}


async function bootstrap() {
  const port = 3333;

  const app = await NestFactory.create(AppModule, {
    logger: WinstonModule.createLogger({
      level: 'debug',
      format: winston.format.combine(   /* This is required to get errors to log with stack traces. See https://github.com/winstonjs/winston/issues/1498 */
        winston.format.errors({ stack: true }),
        winston.format.json(),
      ),
      defaultMeta: {
        application: 'Helios API',
        environment: process.env.NODE_ENV || 'development',
      },
      transports: [
        // log everything to the console
        new winston.transports.Console({
          format: winston.format.combine(
            winston.format.colorize({
              all:true
            }),
            winston.format.simple()
          )
        }),
        new SeqTransport({
          serverUrl: "http://localhost:5341",
          apiKey: undefined,
          onError: (e => { console.error(e) }),
          handleExceptions: true,
          handleRejections: true,
        })
      ],
    }),
    cors: {
      // todo: make this work in production with a configurable origin
      origin: 'http://localhost:4200',
      credentials: true,
    },
  });

  const adapterHost = app.get(HttpAdapterHost);

  setupSwagger(app, 'docs');

  app.useGlobalPipes(
    new ValidationPipe({
      transform: true,
    }),
  );

  app.useGlobalInterceptors(new TransformInterceptor());
  app.useGlobalFilters(new HttpExceptionLogger(adapterHost));
  await app.listen(port);

  Logger.log(`🚀 Application is running on: http://localhost:${port}/`);
}

bootstrap();
