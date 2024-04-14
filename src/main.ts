import { HttpAdapterHost, NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { INestApplication, Logger, ValidationPipe } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';
import { TransformInterceptor } from './core/interceptors/class-transform.interceptor';
import { HttpExceptionLogger } from './core/filters/http-exception-logger-filter/http-exception-logger.filter';
import { WinstonModule } from 'nest-winston';
import { SeqTransport } from '@datalust/winston-seq';
import * as winston from 'winston';

/**
 * Setup Swagger API generation
 * @param app
 * @param swaggerUrl The url to serve the swagger documentation on
 */
const setupSwagger = (app: INestApplication, swaggerUrl: string) => {
  const swaggerConfig = new DocumentBuilder()
    .setTitle('Helios API')
    .setDescription('De Helios API')
    .setVersion('1.0')
    .addBearerAuth()
    .build();

  const document = SwaggerModule.createDocument(app, swaggerConfig);
  SwaggerModule.setup(swaggerUrl, app, document);
};

/**
 * Create a logger for the application using Winston instead of the built-in nestjs logger.
 * Allows for logging to multiple transports, such as the console and Seq, or modifying the log format.
 */
const createLogger = () => WinstonModule.createLogger({
  // todo take from config file?
  level: 'debug',
  format: winston.format.combine(   /* This is required to get errors to log with stack traces. See https://github.com/winstonjs/winston/issues/1498 */
      winston.format.errors({stack: true}),
      winston.format.json(),
  ),
  defaultMeta: {
    Application: 'Helios API',
    Environment: process.env.NODE_ENV || 'Local',
  },
  transports: [
    // log everything to the console
    new winston.transports.Console({
      format: winston.format.combine(
          winston.format.colorize({
            all: true,
          }),
          winston.format.simple(),
      ),
    }),
    new SeqTransport({
      serverUrl: 'http://localhost:5341',
      apiKey: undefined,
      onError: (e => {
        console.error(e);
      }),
      handleExceptions: true,
      handleRejections: true,
    }),
  ],
});

async function bootstrap() {
  const port = 3333;

  const app = await NestFactory.create(AppModule, {
    logger: createLogger(),
    cors: {
      // todo: make this work in production with a configurable origin
      origin: 'http://localhost:4200',
      credentials: true,
    },
  });

  const adapterHost = app.get(HttpAdapterHost);

  setupSwagger(app, 'docs');

  // Pipes are used to transform the incoming request data BEFORE it reaches the route handler.
  app.useGlobalPipes(
    new ValidationPipe({
      transform: true,
    }),
  );

  // Interceptors are used to intercept the response and apply actions to it before it is sent back to the client.
  app.useGlobalInterceptors(new TransformInterceptor());
  app.useGlobalFilters(new HttpExceptionLogger(adapterHost));
  await app.listen(port);

  Logger.log(`🚀 Application is running on: http://localhost:${port}/`);
}

bootstrap();
