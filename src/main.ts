import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { INestApplication, Logger } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';

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
    cors: {
      // todo: make this work in production with a configurable origin
      origin: 'http://localhost:4200',
      credentials: true,
    },
  });

  setupSwagger(app, 'docs');

  await app.listen(port);

  Logger.log(`🚀 Application is running on: http://localhost:${port}/`);
}

bootstrap();
