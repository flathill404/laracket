#!/bin/bash

# Create S3 bucket
awslocal s3 mb s3://local-bucket

# Set public bucket policy
awslocal s3api put-bucket-policy --bucket local-bucket --policy '{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadWrite",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:*",
      "Resource": [
        "arn:aws:s3:::local-bucket",
        "arn:aws:s3:::local-bucket/*"
      ]
    }
  ]
}'

# Set CORS configuration
awslocal s3api put-bucket-cors --bucket local-bucket --cors-configuration '{
  "CORSRules": [
    {
      "AllowedHeaders": ["*"],
      "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
      "AllowedOrigins": ["*"],
      "ExposeHeaders": ["ETag"],
      "MaxAgeSeconds": 3000
    }
  ]
}'

# Verify SES identity
awslocal ses verify-email-identity --email-address no-reply@example.com
