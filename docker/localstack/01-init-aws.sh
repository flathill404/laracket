#!/bin/bash

# Create S3 bucket
awslocal s3 mb s3://local-bucket

# Verify SES identity
awslocal ses verify-email-identity --email-identity hello@example.com
